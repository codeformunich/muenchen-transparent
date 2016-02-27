<?php


/**
 * @var $this RISBaseController
 * @var string $content
 */

$this->context->beginContent('//layouts/main');
?>
	<div id="content" class="col-lg-12 col-md-12">
		<?php echo $content; ?>
	</div>
<?php $this->context->endContent(); ?>