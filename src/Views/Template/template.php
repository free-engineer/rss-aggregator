<script type='text/javascript'>
	/* all check or uncheck */
	function toggleAllCheckbox( isChecked ) {
		var checkboxes = document.rss_form.opml_target;
		for ( var i=0; i<checkboxes.length; i++) {
			checkboxes[i].checked = isChecked;
		}
	}

	function getCheckedCount( elm ) {
		counter = 0;
		for( i=0; i < elm.length; i++){
			if( elm[i].checked ) counter++;
		}
		return counter;
	}

	function handleDownload() {
		/* check count */
		if ( getCheckedCount( document.getElementsByName('opml_target') ) == 0 ) {
			alert('チェックされていません');
			return false;
		}
		var content = getUrlList();
		//alert(content);
		//return false;
		const opml_filename = document.getElementById("download").getAttribute("download");

		var blob = new Blob([ content ], { "type" : "text/xml" });

		if (window.navigator.msSaveBlob) { 
			window.navigator.msSaveBlob(blob, opml_filename); 

			// msSaveOrOpenBlobの場合はファイルを保存せずに開ける
			window.navigator.msSaveOrOpenBlob(blob, opml_filename); 
		} else {
			document.getElementById("download").href = window.URL.createObjectURL(blob);
		}
	}
	function getUrlList() {
		urlList = [];
		selected_count = 0;
		checkUrls = document.getElementsByName('opml_target');
		for( i = 0; i < checkUrls.length; i++ ) {
			if( checkUrls[i].checked ) {
				urlList.push(checkUrls[i].value);
				selected_count++;
			}
		}
		// alert(urlList);
		return getOPML(urlList);
	}
	function getOPML(urls) {
		strBuf = "";
		strBuf += '\<\?' + 'xml version="1.0" encoding="utf-8"' + '\?\>\n';
		strBuf += '<opml version="2.0">\n';
		strBuf += '  <head>\n';
		strBuf += '    <title>mySubscriptions.opml</title>\n';
		strBuf += '    <dateCreated>Mon, 25 June 2018 20:00:00 +0900</dateCreated>\n';
		strBuf += '    <dateModified>Tue, 25 Dec 2018 20:39:42 +0900</dateModified>\n';
		strBuf += '    <ownerName>Free Engineer S</ownerName>\n';
		strBuf += '    <ownerEmail>s.free.engineer@gmail.com</ownerEmail>\n';
		strBuf += '    <ownerId>https://free-engineer.xrea.jp/</ownerId>\n';
		strBuf += '  </head>\n';
		strBuf += '  <body>\n';
		for( i = 0; i < urls.length; i++ ) {
			arr_channel_info = urls[i].split('||');
			strBuf += '    <outline text="' + arr_channel_info[0] + '" title="' + arr_channel_info[0] + '" description="' + arr_channel_info[1] + '" type="rss" language="ja" xmlUrl="' + arr_channel_info[2] + '" htmlUrl="' + arr_channel_info[3] + '" />\n';
		}
		strBuf += '  </body>\n';
		strBuf += '</opml>\n';

		return strBuf;
	}
</script>

<h1 class="uk-text-center"><span>Tech系日本語Podcast(ポッドキャスト)OPML自動生成</span></h1>
<h2 class="uk-heading-line uk-text-center"><span>Japanese Podcasts for engineers with OPML generator</span></h2>

<form name='rss_form'>
<div class="uk-section">
<div class="uk-container uk-container-small">
<div class="uk-flex uk-flex-center">
	<div class='opmlbutton uk-flex uk-flex-column'>
		<div class='download-button'>
			<!-- When use button tag and uk-button class, Blob download faild. So Changed to normal div tags.-->
			<a class='' id="download" href="#" download="techie-podcasts.opml" onclick="handleDownload()"><b>OPMLファイルをダウンロードする</b></a>
		</div>
		<div class='checkall uk-align-center'><input id='checkall' type="checkbox" checked='checked' onClick='toggleAllCheckbox(checkall.checked)'/><label for='checkall'>全チェック／全解除</label></div>
	</div>
</div>
<?php
function getFormattedDate($dt, $withTime = true){
	$time_format = $withTime ? ' H:m' : '';
	if (date('Y', strtotime($dt)) === date('Y')) {
		// Same year, omit the YYYY.
		return date('m/d'.$time_format, strtotime($dt));
	} else {
		return date('Y/m/d'.$time_format, strtotime($dt));
	}
}
?>

<?php foreach ($items as $item) : ?>
    <?php
    $channel = $item[0];
    $episodes = $item[1];
    ?>
<div class="uk-card uk-card-default uk-card-body uk-padding-small">
    <div class='uk-grid'>
        <div class="rss-img left uk-width-1-4">
            <img src='%{$channel->getImage()}' />
        </div>
        <div class='uk-child-width-expand@s uk-width-3-4'>
            <div class="channel-info">
                <h2 class="channel-title"><span class="marking">
                    <input class="opml_checkbox" type="checkbox" id="%{$channel->getLink()}" name="opml_target" checked="checked" value="%{$channel->getTitle()}||%{$channel->getDescription()}||%{$channel->getSubscribeUrl()}||%{$channel->getLink()}">
                    <label for="%{$channel->getLink()}">%{($channel->getTitle()) ? $channel->getTitle() : $channel->getLink()}</label></span>
                </h2>
                <h3 class="channel-desc">%{$channel->getDescription()}</h3>
            </div>
        </div>
		<div class='uk-width-1'>
			<div class="channel-meta uk-align-right">
				<span class="channel-pubdate">最終配信 : %{getFormattedDate($channel->getPubdate())}</span>
				<button class="uk-button uk-button-danger uk-button-small">
				<a href="%{$channel->getSubscribeUrl()}" target="_blank" uk-icon="icon: rss; ratio: 0.8;">RSS&nbsp;</a>
				</button>
				<button class="uk-button uk-button-primary uk-button-small">
				<a href="%{$channel->getLink()}" target="_blank" class="external" uk-icon="icon: home; ratio: 0.8;">Web&nbsp;</a>
				</button>&nbsp;
			</div>
		</div>
    </div>

    <div class="item-area">
        <ul class="episode-info" uk-accordion='multiple: true'>
    <?php foreach ($episodes as $episode) : ?>
            <li>
				<a class="uk-accordion-title" href="#">
				<div class='uk-flex'>
				<div class="episode-pubdate">%{getFormattedDate($episode->getPubdate(), false)}</div>
				<div class='episode-title'>%{$episode->getTitle()}</div>
				</div>
				</a>
				<div class='uk-accordion-content'>
					<div class="episode-description">%{strip_tags($episode->getDescription())}
						<div class='uk-align-right'><button class='uk-button uk-button-primary uk-button-small'><a class="readmore" href="%{$episode->getPermalink()}" target="_blank">[続きを読む…]</a></button></div>
					</div>
				</div>
            </li>
    <?php endforeach;?>   
        </ul>
    </div>
</div>
<p></p>
<?php endforeach;?>
</div>
</div>
</form>