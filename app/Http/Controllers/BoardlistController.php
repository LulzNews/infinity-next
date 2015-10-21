<?php namespace App\Http\Controllers;

use App\Board;
use App\BoardTag;

use App\Http\Controllers\Board\BoardStats;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

use Input;
use Request;

class BoardlistController extends Controller {
	
	use BoardStats;
	
	/*
	|--------------------------------------------------------------------------
	| Boardlist Controller
	|--------------------------------------------------------------------------
	|
	|
	|
	*/
	
	/**
	 * View file for the main index page container.
	 *
	 * @var string
	 */
	const VIEW_INDEX = "boardlist";
	
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$boards = $this->boardListSearch();
		$stats  = $this->boardStats();
		$tags   = $this->boardListTags();
		
		if (Request::wantsJson())
		{
			$input = $this->boardListInput();
			$items = new Collection($boards->items());
			$items = $items->toArray();
			
			foreach ($items as &$item)
			{
				unset($item['stats']);
			}
			
			return json_encode([
				'boards'   => $items,
				'current_page' => (int) $boards->currentPage(),
				'per_page' => (int) $boards->perPage(),
				'total'    => $boards->total(),
				'omitted'  => (int) max(0, $boards->total() - ($boards->currentPage() * $boards->perPage())),
				'tagWeght' => $tags,
				'search'   => [
					'lang'  => $input['lang'] ?: "",
					'page'  => $input['page'] ?: 1,
					'tags'  => $input['tags'] ?: [],
					'time'  => Carbon::now()->timestamp,
					'title' => $input['title'] ?: "",
					'sfw'   => !!$input['sfw'],
				]
			]);
		}
		
		return $this->view(static::VIEW_INDEX, [
			'boards' => $boards,
			'stats'  => $stats,
			'tags'   => $tags,
		]);
	}
	
	protected function boardListInput()
	{
		$input = Input::only('page', 'sfw', 'title', 'lang', 'tags');
		
		$input['page']  = isset($input['page'])  ? max((int) $input['page'], 1) : 1;
		$input['sfw']   = isset($input['sfw'])   ? !!$input['sfw'] : false;
		$input['title'] = isset($input['title']) ? $input['title'] : false;
		$input['lang']  = isset($input['lang'])  ? $input['lang'] : false;
		
		if (isset($input['tags']))
		{
			$input['tags'] = str_replace(["+", "-", " "], ",", $input['tags']);
			$input['tags'] = array_filter(explode(",", $input['tags']));
		}
		else
		{
			$input['tags'] = [];
		}
		
		return $input;
	}
	
	protected function boardListSearch($perPage = 25)
	{
		$input = $this->boardListInput();
		
		$title = $input['title'];
		$page  = $input['page'];
		$tags  = $input['tags'];
		$sfw   = $input['sfw'];
		
		$boards = Board::getBoardsForBoardlist();
		$boards = $boards->filter(function($item) use ($tags, $sfw, $title) {
				// Are we able to view unindexed boards?
				if (!$item->is_indexed && !$this->user->canViewUnindexedBoards())
				{
					return false;
				}
				
				// Are we requesting SFW only?
				if ($sfw && !$item->is_worksafe)
				{
					return false;
				}
				
				if ($tags && count(array_intersect($tags, $item->tags->pluck('tag')->toArray())) < count($tags))
				{
					return false;
				}
				
				if ($title && stripos($item->board_uri, $title) === false && stripos($item->title, $title) === false && stripos($item->description, $title) === false)
				{
					return false;
				}
				
				return true;
			});
		
		if ($title)
		{
			$boards = $boards->sort(function($a, $b) use ($title) {
				// Sort by active users, then last post time.
				$aw = 0;
				$bw = 0;
				
				if ($a->board_uri === $title)
				{
					$aw += 8;
				}
				if (stripos($a->board_uri, $title) !== false)
				{
					$aw += 4;
				}
				if (stripos($a->title, $title) !== false)
				{
					$aw += 2;
				}
				if (stripos($a->description, $title) !== false)
				{
					$aw += 1;
				}
				
				if ($b->board_uri === $title)
				{
					$bw += 8;
				}
				if (stripos($b->board_uri, $title) !== false)
				{
					$bw += 4;
				}
				if (stripos($b->title, $title) !== false)
				{
					$bw += 2;
				}
				if (stripos($b->description, $title) !== false)
				{
					$bw += 1;
				}
				
				return $bw - $aw;
			});
		}
		
		$paginator = new LengthAwarePaginator(
			$boards->forPage($page, $perPage),
			$boards->count(),
			$perPage,
			$page
		);
		$paginator->setPath("boards.html");
		
		foreach ($input as $inputIndex => $inputValue)
		{
			if ($inputIndex == "sfw")
			{
				$inputIndex = (int) !!$inputValue;
			}
			
			$paginator->appends($inputIndex, $inputValue);
		}
		
		
		return $paginator;
	}
	
	protected function boardListTags()
	{
		$tags = BoardTag::distinct('tag')->with([
			'boards',
			'boards.stats',
			'boards.stats.uniques',
		])->get();
		
		$tagWeight = [];
		
		foreach ($tags as $tag)
		{
			$tagWeight[$tag->tag] = $tag->getWeight(3);
			
			if ($tag->getWeight() > 0)
			{
				
			}
		}
		
		return $tagWeight;
	}
	
}
