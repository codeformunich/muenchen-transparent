<?php

class OParlController extends CController {


	/**
	 */
	public function actionSystem() {
		Header( "Content-Type: application/json" );
		echo json_encode( OParl10::encodeSystem() );
	}

	/**
	 */
	public function actionBodyList() {
		Header( "Content-Type: application/json" );

		$bodies = [ ];

		$bas = Bezirksausschuss::model()->findAll();
		foreach ( $bas as $ba ) {
			$bodies[] = OParl10::encodeBABody( $ba );
		}

		echo json_encode( [
			'items'        => $bodies,
			'itemsPerPage' => 100,
		] );
	}

	/**
	 * @param int $body
	 */
	public function actionBody( $body ) {
		Header( "Content-Type: application/json" );

		$ba = Bezirksausschuss::model()->findByPk( $body );
		echo json_encode( OParl10::encodeBABody( $ba ) );
	}


	/**
	 * @param int $body
	 *
	 * @return array
	 */
	public function actionOrganizationList( $body ) {
		Header( "Content-Type: application/json" );

		$organizations = [ ];

		$body    = ( $body > 0 ? IntVal( $body ) : null );
		$gremien = Gremium::model()->findAllByAttributes( [ 'ba_nr' => $body ] );
		foreach ( $gremien as $gremium ) {
			$organizations[] = OParl10::encodeGremium( $gremium );
		}
		$fraktionen = Fraktion::model()->findAllByAttributes( [ 'ba_nr' => $body ] );
		foreach ( $fraktionen as $fraktion ) {
			$organizations[] = OParl10::encodeFraktion( $fraktion );
		}

		echo json_encode( [
			'items'        => $organizations,
			'itemsPerPage' => 100,
		] );
	}

	/**
	 * @param int $body
	 */
	public function actionPaperList( $body ) {
		Header( "Content-Type: application/json" );

		echo json_encode( [ ] ); // @TOOD
	}

	/**
	 * @param int $body
	 * @param int $paper
	 */
	public function actionPaper( $body, $paper ) {
		Header( "Content-Type: application/json" );

		echo json_encode( [ ] ); // @TODO
	}
}
