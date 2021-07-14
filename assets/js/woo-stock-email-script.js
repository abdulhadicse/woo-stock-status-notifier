;(function($){

    $(document).ready( function() {
        $( '#contact' ).on( 'submit', function( e ) {
            e.preventDefault();

            var data = $( this ).serialize();

            $.post( wssn.url, data, function( response ){
                if(  response.data ) {
                    $('#messgae').text( response.data.message );
                    $('#email').val('');
                }
            } )
            .fail( function(){
                console.log( 'data inserted fialed' );
            } );  
        })
    });

})(jQuery);