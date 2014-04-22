<div id="ajax-filelist-loader">
	<div id="fileme-filelist" class="fileme-files-container">
		<table class="fileme-filelist">
			<thead>
				<tr>
					<th>Filename</th>
					<th>Info</th>
					<th>Last Activity Time</th>
					<th>Size</th>
					<th>Options</th>
				</tr>
			</thead>
			<tbody>
			{if isset($items)}
				{foreach $items as $item}
				<tr>
					<td class="fileme-item-name">
						<a href="{$item->url}"{if $item->type == 'directory'} class="fileme-directory"{/if}{if $item->type == 'file'} title="Download this file"{/if}><i class="icon {if $item->type == 'directory'} fileme-ico-folder{/if} fileme-ico-default-ext{if $item->ext != ''} fileme-ico-{$item->ext}{/if}"></i> {$item->name}</a>
					</td>
					<td>
						{$item->ext}, {$item->mime}, {$item->type}, {$item->permission}
					</td>
					<td>
						{$item->modified|cms_date_format}
					</td>
					<td>
						{$item->size}
					</td>
					<td>
						<a href="#" class="fileme-button fileme-item-edit"><i class="fileme-ico-edit"></i></a> 
						<a href="#" class="fileme-button fileme-item-options"><i class="fileme-ico-gears"></i></a>
					</td>
				</tr>
				{/foreach}
			{else}
				<tr>
					<td colspan="5">This directory is empty</td>
				</tr>
			{/if}
			</tbody>
		</table>
	</div>
</div>