<div class="post-container {{ is_null($post->reply_to) ? 'op-container' : 'reply-container' }} {{ $post->hasBody() ? 'has-body' : 'has-no-body' }} {{ count($post->attachments) > 1 ? 'has-files' : count($post->attachments) > 0 ? 'has-file' : 'has-no-file' }}"
	data-widget="post"
	data-post_id="{{ $post->post_id }}"
	data-board_uri="{{ $post->board_uri }}"
	data-board_id="{{ $post->board_id }}"
	data-created-at="{{ $post->created_at->timestamp }}"
	data-updated-at="{{ $post->updated_at->timestamp }}"
	data-capcode="{{ $post->capcode_capcode ? $post->capcode_role : '' }}"
	id="post-{{$post->board_uri}}-{{$post->board_id}}"
>
	@set('catalog',    isset($catalog) && $catalog ? true : false)
	@set('multiboard', isset($multiboard) ? $multiboard : false)
	@set('preview',    isset($preview)    ? $preview    : (!isset($updater) || !$updater) && $post->body_too_long )
	@set('reply_to',   isset($reply_to) && $reply_to ? $reply_to : false)
	
	@if ($multiboard && !$reply_to)
	@include('content.board.crown', [
		'board'  => $post->board,
	])
	@endif
	
	<div class="post-interior">
		@if ($post->reports)
		@include('content.board.post.single', [
			'board'   => $board,
			'post'    => $post,
			'catalog' => isset($catalog) ? !!$catalog : false,
		])
		
		{{-- Each condition for an item must also be supplied as a condition so the <ul> doesn't appear inappropriately. --}}
		@if ($preview || $post->bans->count() || $post->updated_by)
		<ul class="post-metas">
			@if ($preview)
			<li class="post-meta meta-see_more">@lang('board.preview_see_more', [
				'url' => $post->getURL(),
			])</li>
			@endif
			
			@if ($post->bans)
			@foreach ($post->bans as $ban)
			<li class="post-meta meta-ban_reason">
				@if ($ban->justification != "")
				<i class="fa fa-ban"></i> @lang('board.meta.banned_for', [ 'reason' => $ban->justification ])
				@else
				<i class="fa fa-ban"></i> @lang('board.meta.banned')
				@endif
			</li>
			@endforeach
			@endif
			
			@if ($post->updated_by)
			<li class="post-meta meta-updated_by">
				<i class="fa fa-pencil"></i> @lang('board.meta.updated_by', [ 'name' => $post->updated_by_username, 'time' => $post->updated_at ])
			</li>
			@endif
		</ul>
		@endif
		@else
			Post was hidden from view.
		@endif
	</div>
</div>
