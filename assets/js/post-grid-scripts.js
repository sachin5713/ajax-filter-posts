jQuery(document).ready(function($) {
        
    // Post loading
let ids = [];

// jQuery(document).on('click', 'ul .asr_texonomy', function(){
//         let $this = jQuery(this);
//         var term_id = $this.attr('data_id');        
//         var type = $(this).data('type');
//         if($(this).hasClass('all') == true){
//             ids = [];
//             $('.content_top_cat').removeClass('active');
//         }else{
//             if(term_id != 0){
//                 if(ids.indexOf(term_id) === -1){
//                     ids.push(term_id);
//                 }else{
//                     ids.splice(ids.indexOf(term_id),1);
//                 }
//             }
//         }
     
//         if( !$this.hasClass('active') ) {
//             // $this.addClass('active').siblings().removeClass('active');
//             $('li.all').removeClass('active');

//             $this.addClass('active');
            
//             // Load Grid
//             // asr_ajax_get_postdata(term_id, $this, '','',type);
//             asr_ajax_get_postdata(ids, $this, '','',type);
//         }
//         else {
//              $this.removeClass('active');
//              if($('.content_top_cat.active').length === 0){
//                 $('li.all').addClass('active');
//              }
//               asr_ajax_get_postdata(ids, $this, '','',type);
//         }

//     });

        // jQuery(document).ready(function() {
          
          

        var text = [];
        jQuery(".tags .asr_texonomy, ul .asr_texonomy").click(function(){

            var $this   = jQuery(this);
            var type    = $this.data('type');
            var data_text    = $this.data('text');
            var term_id = $this.attr('data_id');
            $this.toggleClass('active');



            if($this.hasClass('active')){
                ids.push(term_id);
            } else {
                var index = ids.indexOf(term_id);
                if (index > -1) {
                  ids.splice(index, 1);
                }
            }

            if(type != 'terms'){
                if($this.hasClass('active')){
                    if ($this.hasClass("all")){
                        text = [];
                    } else {
                        text.push(data_text);
                    }
                } else {
                    var tindex = text.indexOf(data_text);
                    if (tindex > -1) {
                      text.splice(tindex, 1);
                    }       
                }
                var mapped_hash = $.map(text, function(value) {
                  return '#' + value;
                }).join(', ');
                jQuery('.tagname_byfilter_dec').html(mapped_hash);
            }

            if ($this.hasClass("all")){
                $this.addClass("active");
                ids = [];
                jQuery('.content_top_cat, .tags_category').not(this).removeClass('active');
            } 

            if(ids == ''){
                $('.all').addClass('active');
            } else {
                $('.all').removeClass('active');
            }

            asr_ajax_get_postdata(ids, $this, '','',type);
        });

    // Pagination
    jQuery( document ).on('click', '.prev_btn, .next_btn', function(e){
        e.preventDefault();
        var ids = [];
        var term_id = "-1";
        let $this = jQuery(this);

        // var paged = $this.text();
        var paged = '';
        var loadMore = false;

        // Try infinity loading
        if ( $this.hasClass('am-post-grid-load-more') ) {
            paged = $this.data('next');
            loadMore = true;
        } else {
            paged = $this.data('page');
        }

        var theSelector = $this.closest('.am_ajax_post_grid_wrap').find('.asr_texonomy');

        var theTags = $this.closest('.am_ajax_post_grid_wrap').find('.tags_category');

        var activeSelector = $this.closest('.am_ajax_post_grid_wrap').find('.asr_texonomy.active');

        // if( activeSelector.length > 0 ){
        //     term_id = activeSelector.attr('data_id');
        //     ids.push(term_id);
        // } else {
        //     activeSelector = theSelector;
        // }
        jQuery.each(theSelector, function(key,val) {
            if(jQuery(this).hasClass('active')){
                ids.push(jQuery(this).attr('data_id'));
            } else if(jQuery(this).hasClass('all')){
                ids = [];
            }
        });
        jQuery.each(theTags, function(key,val) {
            if(jQuery(this).hasClass('active')){
                ids.push(jQuery(this).attr('data_id'));
            } else if(jQuery(this).hasClass('all')){
                ids = [];
            }
        });
        asr_ajax_get_postdata(ids, activeSelector, paged, loadMore);

    });
        // Pagination
    // jQuery( document ).on('click', '.page-numbers', function(e){
    //    // e.preventDefault();

    //     var term_id = "-1";
    //     let $this = jQuery(this);

    //     var paged = $this.data('page');
    //     var loadMore = false;

        

    
    //     var theSelector = $this.closest('.am_ajax_post_grid_wrap').find('.asr_texonomy');
    //     var activeSelector = $this.closest('.am_ajax_post_grid_wrap').find('.asr_texonomy.active');

    //     if( activeSelector.length > 0 ){
    //         term_id = activeSelector.attr('data_id');
    //     } else {
    //         activeSelector = theSelector;
    //     }

    //     // Load Post Grids
    //     asr_ajax_get_postdata(term_id, activeSelector, paged, loadMore);

    // });

    // Set scroll flag
    var flag = false;

    //ajax filter function
    function asr_ajax_get_postdata(term_ID, selector, paged, loadMore, type){

        // console.log(term_ID); return false;

        var getLayout = jQuery(selector).closest('.am_ajax_post_grid_wrap').find(".asr-filter-div").attr("data-layout");
        var pagination_type = jQuery(selector).closest('.am_ajax_post_grid_wrap').attr("data-pagination_type");
        var jsonData = jQuery(selector).closest('.am_ajax_post_grid_wrap').attr('data-am_ajax_post_grid');
       

        var $args = JSON.parse(jsonData);
        var data = {
            action: 'asr_filter_posts',
            asr_ajax_nonce: asr_ajax_params.asr_ajax_nonce,
            term_ID: term_ID,
            type:type,
            layout: (getLayout) ? getLayout : "1",
            jsonData: jsonData,
            pagination_type: pagination_type,
            loadMore: loadMore,
        }

        if( paged ){
            data['paged'] = paged;
        }

        $.ajax({
            type: 'post',
            url: asr_ajax_params.asr_ajax_url,
            data: data,
            beforeSend: function(data){
                
                if ( loadMore ) {
                    // Loading Animation Start
                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.am-post-grid-load-more').addClass('loading');
                    flag = true;
                } else {
                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.asr-loader').show();
                }
            },
            complete: function(data){
                
                if ( loadMore ) {
                    // Loading Animation End
                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.am-post-grid-load-more').removeClass('loading');
                } else {
                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.asr-loader').hide();
                }
            },
            success: function(data){
                
                if ( loadMore ) {

                    var newPosts = jQuery('.am_post_grid', data).html();
                    var newPagination = jQuery('.am_posts_navigation', data).html();

                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.asrafp-filter-result .am_post_grid').append(newPosts);
                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.asrafp-filter-result .am_posts_navigation').html(newPagination);

                } else {

                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.asrafp-filter-result').hide().html(data).fadeIn(0, function() {
                        //jQuery(this).html(data).fadeIn(300);
                    });
                }

                flag = false;
                jQuery( window ).trigger('scroll');

                // Animation
                if( $args.animation == "true" ){
                    jQuery(selector).closest('.am_ajax_post_grid_wrap').find('.am_grid_col').slideDown();
                }
                
            },
            // error: function(data){
            //     alert('Cannot load the Post Grid.');
            // },

        });
    }

    // Initial Custom Trigger
    jQuery(document).on('am_ajax_post_grid_init', function(){
        
        jQuery('.am_ajax_post_grid_wrap').each(function(i,el){                
            var amPGdata = jQuery(this).data('am_ajax_post_grid');
            if( amPGdata && amPGdata.initial ){
                asr_ajax_get_postdata(amPGdata.initial, jQuery(this).find('.asr-ajax-container'));
            }
        });
    });

    // Handle Infinite scroll
    jQuery( window ).on('scroll', function(e){
        jQuery('.infinite_scroll.am-post-grid-load-more').each(function(i,el){

            var $this = jQuery(this);

            var H = jQuery(window).height(),
                r = el.getBoundingClientRect(),
                t=r.top,
                b=r.bottom;

            var tAdj = parseInt(t-(H/2));

            if ( flag === false && (H >= tAdj) ) {
                //console.log( 'inview' );
                $this.trigger('click');
            } else {
                //console.log( 'outview' );
            }
        });
    });

});

// Load Each Grid on Page Load
window.addEventListener('load', (event) => {
    // jQuery(document).trigger('am_ajax_post_grid_init');
    // console.log('on load triggered')
});