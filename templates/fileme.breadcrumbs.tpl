<div id="ajax-breadcrumbs-loader" class="fileme-breadcrumb">
	<ul id="fileme-breadcrumb-navigation" class="no-list no-margin no-padding fileme-breadcrumb-navigation">
		<li><span><i class="fileme-ico-folder-open"></i></span></li>
		{foreach $breadcrumbs as $breadcrumb}
			{if $breadcrumb@last}
				<li class="fileme-active-dir">
					<span>{$breadcrumb->name}</span>
				</li>
			{else}
				<li>
					<a href="{$breadcrumb->url}" class="fileme-js-action fileme-change-dir">{$breadcrumb->name} /</a>
				</li>
			{/if}
		{/foreach}
	</ul>
</div>