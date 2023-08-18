/*
 * for pdf delete function
 */
const delete_pdf = document.querySelector( '.delete-pdf' );
const delete_dialog = document.querySelector( '.delete-dialog' );
const delete_button_close = document.querySelector( '.delete-button-close' );
const delete_button_cancel = document.querySelector( '.delete-button-cancel' );

if ( delete_pdf !== null ) {
	delete_pdf.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		delete_pdf.classList.add( 'is-open' );
		delete_dialog.showModal();
		return false;
	} );
}
if ( delete_dialog !== null ) {
	delete_button_close.addEventListener( 'click', ( e ) => {
		delete_dialog.close();
	} );
}
if ( delete_button_cancel !== null || delete_button_overlay ) {
	delete_button_cancel.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		delete_dialog.close();
		delete_pdf.classList.remove( 'is-open' );
	} );
}

/*
 * upload buttn judgment
 */
const file_select_status = document.querySelector( '.rddp-file' );
// console.log( file_select_status );
console.log( file_select_status.files.length );
