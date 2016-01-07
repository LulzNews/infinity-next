@if ($post->attachments->count())
@spaceless
<ul class="post-attachments attachment-count-{{ $post->attachments->count() }} {{ $post->attachments->count() > 1 ? "attachments-multi" : "attachments-single" }}">
	@foreach ($post->attachments as $attachment)
	<li class="post-attachment">
		@if (!isset($catalog) || !$catalog)
		<div class="attachment-container">
			@if ($attachment->isDeleted())
			<figure class="attachment attachment-deleted">
				{!! $attachment->getThumbnailHTML($board) !!}
			</figure>
			@else
			<a class="attachment-link"
				target="_blank"
				href="{!! $attachment->getDownloadURL($board) !!}"
				data-download-url="{!! $attachment->getDownloadURL($board) !!}"
				data-thumb-url="{!! $attachment->getThumbnailURL($board) !!}"
			>
				<figure class="attachment attachment-type-{{ $attachment->guessExtension() }} {{ $attachment->getThumbnailClasses() }}" data-widget="lazyimg">
					{!! $attachment->getThumbnailHTML($board) !!}
					
					<figcaption class="attachment-details">
						<p class="attachment-detail">
							@if ($attachment->pivot->is_spoiler)
							<span class="detail-item detail-filename filename-spoilers">@lang('board.field.spoilers')</span>
							@else
							<span class="detail-item detail-filename filename-cleartext" title="{{ $attachment->pivot->filename }}">{{ $attachment->getShortFilename() }}</span>
							@endif
						</p>
					</figcaption>
				</figure>
			</a>
			
			<div class="attachment-action-group">
				<a class="attachment-action attachment-download" target="_blank" href="{!! $attachment->getDownloadURL($board) . "?disposition=attachment" !!}" download="{!! $attachment->getDownloadName() !!}">
					<i class="fa fa-download"></i>
					<span class="detail-item detail-download">@lang('board.field.download')</span>
					<span class="detail-item detail-filesize">{{ $attachment->getHumanFilesize() }}</span>
					<span class="detail-item detail-filedim" title="{{ $attachment->getFileDimensions() }}">{{ $attachment->getFileDimensions() }}</span>
				</a>
			</div>
			
			<div class="attachment-action-group">
				@if ($attachment->isSpoiler())
				<a href="{{ $attachment->getUnspoilerURL($board) }}" class="attachment-action attachment-unspoiler" title="@lang('board.field.unspoiler')" data-no-instant>
					<i class="fa fa-question"></i>&nbsp;@lang('board.field.unspoiler')
				</a>
				@else
				<a href="{{ $attachment->getSpoilerURL($board) }}" class="attachment-action attachment-spoiler" title="@lang('board.field.spoiler')" data-no-instant>
					<i class="fa fa-question"></i>&nbsp;@lang('board.field.spoiler')
				</a>
				@endif
				
				<a href="{{ $attachment->getRemoveURL($board) }}" class="attachment-action attachment-remove" title="@lang('board.field.remove')" data-no-instant>
					<i class="fa fa-remove"></i>&nbsp;@lang('board.field.remove')
				</a>
			</div>
			@endif
		</div>
		@else
		<a href="{!! $post->getURL() !!}" data-instant>
			<figure class="attachment attachment-type-{{ $attachment->guessExtension() }}" data-widget="lazyimg">
				{!! $attachment->getThumbnailHTML($board, 150) !!}
			</figure>
		</a>
		@endif
	</li>
	@endforeach
</ul>
@endspaceless
@endif
