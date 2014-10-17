<?
/**
 * @var InfosController $this
 * @var Text[] $eintraege
 */
$this->pageTitle = "Glossar";

?>
<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("infos/soFunktioniertStadtpolitik")) ?>">So funktioniert Stadtpolitik</a><br></li>
		<li class="active">Glossar</li>
	</ul>

	<h1>Glossar</h1>

	<br>
	<br>

	<dl class="glossar dl-horizontal" style="max-width: 850px; margin-left: auto; margin-right: auto;">
		<?
		foreach ($eintraege as $eintrag) {
			echo '<dt id="glossar_eintrag_' . $eintrag->id . '">';
			if ($this->binContentAdmin()) echo ' <a href="' . CHtml::encode($this->createUrl("infos/glossarBearbeiten", array("id" => $eintrag->id))) . '" title="Bearbeiten"><span class="glyphicon glyphicon-pencil"></span></a>';
			echo CHtml::encode($eintrag->titel);
			echo '</dt>';
			echo '<dd>' . $eintrag->text . '</dd>';
		}
		?>
	</dl>
	<?

	if ($this->binContentAdmin()) {
		?>
		<div style="text-align: center;"><a href="#" id="glossar_anlegen_caller">
				<span class="glyphicon glyphicon-plus"></span> Neuen Eintrag anlegen
			</a></div>
		<form method="POST" action="<?= CHtml::encode(Yii::app()->createUrl("infos/glossar")) ?>" role="form" class="well"
			  style="max-width: 850px; margin-top: 50px; margin-left: auto; margin-right: auto; display: none;" id="glossar_anlegen_form">
			<h3>Neuen Eintrag anlegen</h3>

			<div class="form-group">
				<label for="glossary_new_title">Begriff</label>
				<input type="text" name="titel" class="form-control" id="glossary_new_title" placeholder="Zu erklärender Begriff" required>
			</div>

			<div class="form-group">
				<label for="glossary_new_text">Erkärung</label>

				<textarea id="glossary_new_text" name="text" cols="80" rows="10"></textarea>
			</div>

			<div style="text-align: center;">
				<button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("anlegen") ?>">Anlegen</button>
			</div>
		</form>

		<script src="/js/ckeditor/ckeditor.js"></script>
		<script>
			$(function () {
				$("#glossar_anlegen_caller").click(function (ev) {
					ev.preventDefault();
					$(this).hide();
					$("#glossar_anlegen_form").show();

					var $edit = $("#glossary_new_text");
					CKEDITOR.replace($edit[0], {
						removePlugins: 'save,backup,about,pastefromword,pastetext,print,preview,templates,newpage',
						extraPlugins: 'a11yhelp,codemirror,enterkey,font,format,justify,basicstyles,blockquote,colorbutton,colordialog,elementspath,filebrowser,horizontalrule,htmlwriter,image,indent,indentblock,indentlist,link,list,listblock,pastefromword,resize,showborders,specialchar,stylescombo,tab,table,tabletools,magicline,floatingspace,removeformat,flash,tableresize,maximize',
						// wordcount,
						docType: '<!DOCTYPE HTML>',
						removeButtons: 'Anchor,Redo',
						contentsLangDirection: 'lrt',

						allowedContent: true,
						floatSpaceDockedOffsetY: 45,
						floatSpaceDockedOffsetX: 0,

						codemirror: {
							showSearchButton: false
						},

						toolbarGroups: [
							{name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
							{name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align']},
							{name: 'colors'},
							{name: 'links'},
							{name: 'clipboard', groups: ['clipboard', 'undo']},
							'/',
							{name: 'tools'},
							{name: 'document', groups: ['mode', 'document', 'doctools']},
							{name: 'styles'},
							{name: 'others'},
							{name: 'editing', groups: ['find', 'selection', 'spellchecker']},
							{name: 'about'},
							'/',
							{name: 'forms'},
							{name: 'insert'}
						],
						indentClasses: ['indent1', 'indent2', 'indent3', 'indent4', 'indent5', 'indent6', 'indent7', 'indent8', 'indent9', 'indent10', 'indent11', 'indent12', 'indent13', 'indent14', 'indent15']

					});

					$("#glossary_new_title").focus();
				});
			});
		</script>
	<?
	}
	?>
</section>
