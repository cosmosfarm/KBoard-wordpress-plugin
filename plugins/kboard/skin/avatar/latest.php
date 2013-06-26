<link rel="stylesheet" href="<?=$skin_path?>/style.css">

<div id="kboard-latest">
	<table>
		<thead>
			<tr>
				<th class="kboard-latest-title">제목</th>
				<th class="kboard-latest-date">작성일</th>
			</tr>
		</thead>
		<tbody>
			<?php while($content = $list->hasNext()):?>
			<tr>
				<td class="kboard-latest-title"><div class="cut_strings"><a href="<?=$url->set('uid', $content->uid)->set('mod', 'document')->toStringWithPath($board_url)?>"><?=$content->title?></a></div></td>
				<td class="kboard-latest-date"><?=date("Y.m.d", strtotime($content->date))?></td>
			</tr>
			<?php endwhile;?>
		</tbody>
	</table>
</div>