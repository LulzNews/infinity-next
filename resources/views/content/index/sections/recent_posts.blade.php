<section id="site-recent-posts" class="grid-50">
	<div class="smooth-box">
		<h2 class="index-title">@lang('index.title.recent_posts')</h2>
		<div class="grid-container">
			<ul class="recent-posts">
				@foreach (App\Post::getRecentPosts(16, false) as $post)
					@set('board_icon', $post->board->getAssetURL('board_icon'))
					
					<li class="recent-post grid-25 tablet-grid-25 mobile-grid-50">
						<span class="recent-post-bg" style="background-image: url('{{ $board_icon }}');"></span>
						<a class="recent-post-link @if ($post->isOp()) recent-post-op @endif" href="{{ $post->getURL() }}"></a>
						<blockquote class="post ugc">
							{!! $post->getBodyFormatted() !!}
						</blockquote>
					</li>
				@endforeach
			</ul>
		</div>
	</div>
</section>