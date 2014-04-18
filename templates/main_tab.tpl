
<div class="pageoptions">
	<ul>
		{foreach $items as $item}
		<li>{$item->name}, {$item->size}, {$item->ext}, {$item->modified|cms_date_format}, {$item->mime}</li>
		{/foreach}
	</ul>
</div>