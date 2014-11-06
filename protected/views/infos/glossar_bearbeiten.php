<?
/**
 * @var InfosController $this
 * @var Text $eintrag
 */
$this->pageTitle = "Glossar bearbeiten";

?>
	<h2>Eintrag bearbeiten</h2>
	<a href="<?= CHtml::encode(Yii::app()->createUrl("infos/glossar")) ?>"><span class="glyphicon glyphicon-arrow-left"></span> Zurück</a><br>

	<form method="POST" action="<?= CHtml::encode(Yii::app()->createUrl("infos/glossarBearbeiten", array("id" => $eintrag->id))) ?>" role="form" class="well"
		  style="max-width: 850px; margin-top: 50px; margin-left: auto; margin-right: auto;">

		<div class="form-group">
			<label for="glossary_new_title">Begriff</label>
			<input type="text" name="titel" class="form-control" id="glossary_new_title" placeholder="Zu erklärender Begriff" value="<?=CHtml::encode($eintrag->titel)?>" required>
		</div>

		<div class="form-group">
			<label for="glossary_new_text">Erkärung</label>

			<textarea id="glossary_new_text" name="text" cols="80" rows="10"><?=CHtml::encode($eintrag->text)?></textarea>
		</div>

		<a href="<?=CHtml::encode($this->createUrl("infos/glossarBearbeiten", array("id" => $eintrag->id, AntiXSS::createToken("del") => "1")))?>" id="eintrag_del_caller" style="color: red; float: right;">
			<span class="glyphicon glyphicon-minus"></span> Eintrag löschen
		</a>

		<div style="text-align: center;">
			<button type="submit" class="btn btn-primary" name="<?= AntiXSS::createToken("speichern") ?>">Speichern</button>
		</div>
	</form>

	<script src="/js/ckeditor/ckeditor.js"></script>
	<script>
		$(function () {
			$("#eintrag_del_caller").click(function(ev) {
				if (!confirm("Diesen Eintrag wirklich löschen?")) ev.preventDefault();
			});

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
		});
	</script>
<?
