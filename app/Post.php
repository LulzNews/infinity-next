<?php namespace App;

use App\Services\ContentFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Post extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['uri', 'board_id', 'reply_to', 'author_ip', 'subject', 'author', 'email', 'body'];
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['author_ip'];
	
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		static::creating(function($post)
		{
			$board = $post->board;
			$board->posts_total += 1;
			$post->board_id = $board->posts_total;
			
			$post->reply_last = $post->freshTimestamp();
			$post->setCreatedAt($post->reply_last);
			$post->setUpdatedAt($post->reply_last);
			
			if ($post->reply_to)
			{
				$op = $post->getOp();
				
				if ($op && $op->canReply())
				{
					$op->reply_last = $post->created_at;
					$op->save();
				}
				else
				{
					return false;
				}
			}
			
			$board->save();
			return true;
		});
	}
	
	
	public function canReply()
	{
		return true;
	}
	
	public function getBodyFormatted()
	{
		$ContentFormatter = new ContentFormatter();
		return $ContentFormatter->formatPost($this);
	}
	
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'uri');
	}
	
	public function op()
	{
		return $this->belongsTo('\App\Post', 'reply_to', 'id');
	}
	
	public function replies()
	{
		return $this->hasMany('\App\Post', 'reply_to', 'id');
	}
	
	
	public function getBoard()
	{
		return $this->board()->get()->first();
	}
	
	public static function getPostForBoard($uri, $board_id)
	{
		return static::where([ 'uri' => $uri, 'board_id' => $board_id ])->first();
	}
	
	public function getOp()
	{
		return $this->op()->get()->first();
	}
	
	public function getReplies()
	{
		return $this->replies()->get();
	}
	
	public function getRepliesForIndex()
	{
		return $this->replies()
			->visible()
			->orderBy('id', 'desc')
			->take(5)
			->get()
			->reverse();
	}
	
	
	public function scopeOp($query)
	{
		return $query->where('reply_to', null);
	}
	
	public function scopeRecent($query)
	{
		return $query->where('created_at', '>=', 'NOW() - 3600');
	}
	
	public function scopeVisible($query)
	{
		return $query->where('deleted_at', null);
	}
}