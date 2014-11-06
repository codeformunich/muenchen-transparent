<?
/**
 * @var InfosController $this
 */
$this->pageTitle = "Impressum";

?>
<section class="well impressum">
	<h1>Impressum</h1>

	<div class="row">
		<div class="col-md-6">
			<h2>Programmiert / Gestaltet von:</h2>
			<ul>
				<li>
					Tobias Hößl
					<a href="https://github.com/CatoTH" title="Tobias Hößl auf Github"><span class="fontello-github-circled"></span></a>
					<a href="https://twitter.com/TobiasHoessl" title="Tobias Hößl auf Twitter"><span class="fontello-twitter"></span></a>
					<a href="https://www.hoessl.eu/" title="Website von Tobias Hößl"><span class="fontello-home"></span></a>
					<a href="http://animexx.onlinewelten.com/steckbriefe/2/" title="Tobias Hößl auf Animexx"><span class="fontello-animexx-karos"></span></a>
				</li>
				<li>
					Bernd (Voller Name?)
					<a href="https://github.com/nepomunich" title="@TODO"><span class="fontello-github-circled"></span></a>
					<a href="https://twitter.com/berndoswald" title="@TODO"><span class="fontello-twitter"></span></a>
				</li>
				<li>
					Konstantin (Voller Name?)
					<a href="https://github.com/konstin" title="@TODO"><span class="fontello-github-circled"></span></a>
				</li>
				<li>
					Dora (Voller Name?)
					<a href="https://github.com/dzdora" title="@TODO"><span class="fontello-github-circled"></span></a>
				</li>
			</ul>
		</div>
		<div class="col-md-6">
			<h2>Verantwortlicher im Sinne von § 5 TMG, § 55 RfStV:</h2>

			Tobias Hößl<br>
			Guido-Schneble-Str. 34<br>
			80689 München<br>
			<br>
			<h2>Kontakt:</h2>

			<a href=<?= $this->createUrl("infos/feedback") ?>>Kontaktformular</a><br>

			Telefon: 0151-56024223<br>

			E-Mail: tobias@hoessl.eu<br>
		</div>
</section>
