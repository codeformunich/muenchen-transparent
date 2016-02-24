<?php

/**
 * @var IndexController $this
 * @var string $feed_title
 * @var array $data
 * @var string $feed_description
 */

$this->layout=false;
header('Content-type: application/xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
	<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
		<channel>
			<atom:link href="<?php echo RISTools::bracketEscape(CHtml::encode(Yii::$app->getBaseUrl(true) . Yii::$app->request->requestUri)); ?>" rel="self" type="application/rss+xml" />
			<title><?php echo Html::encode($feed_title); ?></title>
			<link><?php echo RISTools::bracketEscape(CHtml::encode(Yii::$app->getBaseUrl(true))); ?></link>
			<description><![CDATA[<?php echo $feed_description; ?>]]></description>
			<image>
				<url><?php echo Html::encode(Yii::$app->getBaseUrl(true)); ?>/favicon-192x192.png</url>
				<title><?php echo Html::encode($feed_title); ?></title>
				<link><?php echo Html::encode(Yii::$app->getBaseUrl(true)); ?></link>
			</image>
			<?php foreach ($data as $dat) { ?>
				<item>
					<title><?php echo Html::encode($dat["title"]); ?></title>
					<link><?php echo RISTools::bracketEscape(CHtml::encode(yii::app()->getBaseUrl(true) . $dat["link"])); ?></link>
					<guid><?php echo RISTools::bracketEscape(CHtml::encode(yii::app()->getBaseUrl(true) . $dat["aenderung_guid"])); ?></guid>
					<description><![CDATA[<?php
						echo $dat["content"];
						?>]]></description>
					<pubDate><?php echo date(str_replace("y", "Y", DATE_RFC822), $dat["dateCreated"]); ?></pubDate>
				</item>
			<? } ?>
		</channel>
	</rss>
<?php

Yii::$app->end();

?>