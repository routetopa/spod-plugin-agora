/*$.jStorage.set('abuseCommentFound', false);*/

var SpodpublicCommentsList = function( params ){
    this.$context = $('#' + params.contextId);
    $.extend(this, params, owCommentListCmps.staticData);
        this.$loader = $('.ow_comment_list_loader', this.$context);
}

SpodpublicCommentsList.prototype = {
    init: function(){
    var self = this;
        $('.ow_comments_item', this.$context).hover(function(){$('.cnx_action', this).show();$('.ow_comments_date_hover', this).hide();}, function(){$('.cnx_action', this).hide();$('.ow_comments_date_hover', this).show();});
        this.$loader.one('click',
            function(){
                self.$loader.addClass('ow_preloader');
                $('a', self.$loader).hide();
                self.initialCount += self.loadMoreCount;
                self.reload();
            }
        );

//        OW.bind('base.comments_list_update',
//            function(data){
//                if( data.entityType == self.entityType && data.entityId == self.entityId && data.id != self.cid ){
//                    self.reload();
//                }
//            }
//        );

        OW.trigger('base.comments_list_init', {entityType: this.entityType, entityId: this.entityId}, this);

        OW.bind('base.comment_add', function(data){ if( data.entityType == self.entityType && data.entityId == self.entityId ) self.initialCount++ });

        if( this.pagesCount > 0 )
        {
            for( var i = 1; i <= this.pagesCount; i++ )
            {
                $('a.page-'+i, self.$context).bind( 'click', {i:i},
                    function(event){
                        self.reload(event.data.i);
                    }
                );
            }
        }

        $.each(this.actionArray.comments,
            function(i,o){
                $('#'+i).click(
                    function(){
                        if( confirm(self.delConfirmMsg) )
                        {
                           $(this).closest('div.ow_comments_item').slideUp(300, function(){$(this).remove();});

                            $.ajax({
                                type: 'POST',
                                url: self.delUrl,
                                data: {
                                    cid:self.cid,
                                    commentCountOnPage:self.commentCountOnPage,
                                    ownerId:self.ownerId,
                                    pluginKey:self.pluginKey,
                                    displayType:self.displayType,
                                    entityType:self.entityType,
                                    entityId:self.entityId,
                                    initialCount:self.initialCount,
                                    page:self.page,
                                    commentId:o
                                },
                                dataType: 'json',
                                success : function(data){
                                    if(data.error){
                                            OW.error(data.error);
                                            return;
                                    }

                                    self.$context.replaceWith(data.commentList);
                                    OW.addScript(data.onloadScript);

                                    var eventParams = {
                                        entityType: self.entityType,
                                        entityId: self.entityId,
                                        commentCount: data.commentCount
                                    };

                                    OW.trigger('base.comment_delete', eventParams, this);
                                }
                            });
                        }
                    }
             );
            }
        );

        $.each(this.actionArray.users,
            function(i,o){
                $('#'+i).click(
                    function(){
                        OW.Users.deleteUser(o);
                    }
             );
            }
        );
        
        //ISISLab CODE - Resport abuse from comment management
        $.each(this.actionArray.abuses,
            function(i,o){
                $('#'+i).click(
                    function(){
                        try{
                           var form_content = $("#report-abuse-confirm").children();
                           $("#abuseMessage").html("Il seguente commento risulta inappropriato :\n \"" +  o.message + "\"");
                           $("#commentId").val(o.id);
                                        
                           window.report_abuse_floatbox = new OW_FloatBox({
                                $title: 'Report abuse',
                                $contents: form_content,
                                icon_class: "ow_ic_delete",
                                width: 450
                           });
                        }catch(err){
                           alert(err.message);
                        }
                    }
             );
            }
        );
        
        $.each(this.actionArray.remove_abuses,
            function(i,o){
                $('#'+i).click(
                    function(){
                        try{
                           var form_content = $("#remove-abuse-confirm").children();
                           $("#commentId").val(o.id);
                                        
                           window.remove_abuse_floatbox = new OW_FloatBox({
                                $title: 'Remove abuse',
                                $contents: form_content,
                                icon_class: "ow_ic_delete",
                                width: 450
                           });
                        }catch(err){
                           alert(err.message);
                        }
                    }
             );
            }
        );
        
        //point out abuse comment 
        var abuseCommentId = window.location.href.split("#")[1];
        if(abuseCommentId && !$.jStorage.get('abuseCommentFound')){
           self.findPageForComment(abuseCommentId, 1, this.pages.length - 1);
        } 
        
        //ISISLab CODE - end report abuse management 
        
        for( i = 0; i < this.commentIds.length; i++ )
        {
            if( $('#att'+this.commentIds[i]).length > 0 )
             {
                 $('.attachment_delete',$('#att'+this.commentIds[i])).bind( 'click', {i:i},
                    function(e){

                        $('#att'+self.commentIds[e.data.i]).slideUp(300, function(){$(this).remove();});

                        $.ajax({
                            type: 'POST',
                            url: self.delAtchUrl,
                            data: {
                                cid:self.cid,
                                commentCountOnPage:self.commentCountOnPage,
                                ownerId:self.ownerId,
                                pluginKey:self.pluginKey,
                                displayType:self.displayType,
                                entityType:self.entityType,
                                entityId:self.entityId,
                                page:self.page,
                                initialCount:self.initialCount,
                                loadMoreCount:self.loadMoreCount,
                                commentId:self.commentIds[e.data.i]
                            },
                            dataType: 'json'
                        });
                    }
                 );
             }
        }
    },

    reload:function( page ){
        var self = this;        
        $.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: {
                    cid:self.cid,
                    commentCountOnPage:self.commentCountOnPage,
                    ownerId:self.ownerId,
                    pluginKey:self.pluginKey,
                    displayType:self.displayType,
                    entityType:self.entityType,
                    entityId:self.entityId,
                    initialCount:self.initialCount,
                    loadMoreCount:self.loadMoreCount,
                    page:page
            },
            dataType: 'json',
            success : function(data){
               if(data.error){
                        OW.error(data.error);
                        return;
                }
                self.$loader.removeClass('ow_preloader');
                $('a', self.$loader).hide();
                self.$context.replaceWith(data.commentList);
                OW.addScript(data.onloadScript);
            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                OW.error('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    },
    
    findPageForComment:function (commentId, currentPage, maxPage){
        var self = this;
        $.ajax({
                type: 'POST',
                url: self.respondUrl,
                data: {
                    cid:self.cid,
                    commentCountOnPage:self.commentCountOnPage,
                    ownerId:self.ownerId,
                    pluginKey:self.pluginKey,
                    displayType:self.displayType,
                    entityType:self.entityType,
                    entityId:self.entityId,
                    page: currentPage, 
                    initialCount:self.initialCount,
                    loadMoreCount:self.loadMoreCount,
                    commentId:commentId
                },
                dataType: 'json',
                success : function(data){
                   if(data.error){
                            OW.error(data.error);
                            return;
                    }
                    if(data.commentList.indexOf(commentId) == -1)
                    {
                       if(currentPage <= maxPage ){ 
                           self.findPageForComment(commentId,currentPage + 1, maxPage);
                       }else{
                           return;
                       }
                    }else{
                       $.jStorage.set('abuseCommentFound', true);
                       self.$context.replaceWith(data.commentList);
                       OW.addScript(data.onloadScript);
                       window.location.hash=commentId;
                    }
                    
                },
                error : function( XMLHttpRequest, textStatus, errorThrown ){
                    OW.error('Ajax Error: '+textStatus+'!');
                    throw textStatus;
                }
        });  
   }
}