SPODPUBLICROOM = {};

SPODPUBLICROOM.addRoom = function()
{
    previewFloatBox = OW.ajaxFloatBox('SPODPUBLIC_CMP_PublicRoomCreator', {} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
};

SPODPUBLICROOM.handleSuggestedDataset= function(publicRoomId)
{
    previewFloatBox = OW.ajaxFloatBox('SPODPUBLIC_CMP_Suggestion', {publicRoom : publicRoomId} , {top: '60px', width:'60%', height:'480px', iconClass: 'ow_ic_add', title: ''});
};

SPODPUBLICROOM.addCommnet = function(e)
{
    $(e).parents().eq(5).find("textarea").trigger({type:"comment.test", isButton:true});
};

ODE.addOdeOnComment = function()
{ 
    var ta = $('.ow_comments_input textarea');
    $.each(ta, function(idx, obj) {
        if ( $(obj).attr('data-preview-added') ) {
            return;
        } else {
            $(obj).attr('data-preview-added', true);
        }
        var id = obj.id;

        // Add ODE on Comment
        var odeElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a title="'+ODE.internationalization["add_datalet_"+ODE.user_language]+'" href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'datalet_blue_rect.svg) no-repeat center;" data-id="' + id + '"></a>'));
        odeElem = odeElem.children().first();
        odeElem.click(function (e) {
            ODE.pluginPreview = 'public-room';
            ODE.commentTarget = e.target;
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {width:'90%', height:'90vh', iconClass:'ow_ic_lens', title:''});
        });

        // Add PRIVATE_ROOM on Comment
        if(ODE.is_private_room_active)
        {
            var prElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a title="'+ODE.internationalization["open_my_space_"+ODE.user_language]+'" href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'myspace_blue_rect.svg) no-repeat center;" data-id="' + id + '"></a>'));
            prElem = prElem.children().first();
            prElem.click(function (e) {
                ODE.pluginPreview = 'public-room';
                ODE.commentTarget = e.target;
                $('.ow_submit_auto_click').show();
                document.getElementById('share_from_private_room').dispatchEvent(new Event('animated-button-container-controllet_open-window'));
            });
        }
    });
};