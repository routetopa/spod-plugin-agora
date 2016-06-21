var selected_graph        = null;
var last_selected_element = null;

refreshOpenedDatalets = function(){
    $('div[id^="datalet_placeholder_"]').each(function(key, element){
        try{
            if($($(element).children()[1])[0].refresh != undefined)
               $($(element).children()[1])[0].refresh();
            else
               $($(element).children()[1])[0].behavior.presentData();
        }catch(e){}
    });
};

window.addEventListener('graph-datalet_node-clicked', function(e){
    if(last_selected_element != null){
        last_selected_element.css('border', 'none');
        last_selected_element.css('font-weight', 'normal');
    }

    if(e.detail.node.id == 0) return;

    //current comment container
    var curr_element = $("#comment_" + e.detail.node.originalId);
    //apply border to enphatize it
    $(curr_element).css('border', '1px solid #000000');
    $(curr_element).css('font-weight', 'bold');
    //close all previously opened comments
    $('div[id^="nc_"]').css('display', 'none');
    //close all previously opened datalets
    $('div[id^="datalet_placeholder_"]').css('display', 'none');
    //open the selected datalet
    $('div[id^="datalet_placeholder_' + e.detail.node.originalId + '"]' ).css('display', 'block');
    $('.show_datalet').css('background', '#2196F3');
    //open recursively all parent of the current comment
    $(curr_element).parents('div[id^="nc_"]').css('display', 'block');
    //Resize the selected datalet if there is any
    var datalet = $('div[id^="datalet_placeholder_' + e.detail.node.originalId + '"]' ).children()[1];
    if(datalet != undefined){
        $(datalet)[0].behavior.presentData();
        if($(datalet)[0].refresh != undefined)
            $(datalet)[0].refresh();
        else
            $(datalet)[0].behavior.presentData();
    }

    //scoll the view until the selected comment
    $("#topic_container").scrollTop($(curr_element).offset().top - 50);

    last_selected_element = curr_element;
});

slideGraphPanel = function(){
    $('#graph_container').toggle('slide', {direction: 'right'}, 300, function(){
        $('#topic_container').css('width' , ($('#graph_container').css('display') == 'none') ? '100%' : '50%');
        $('#graphs_buttons_panel').toggle('slide', {direction: 'right'}, 300);
        if($('#graph_container').css('display') == 'none'){
            $("#toolbar-graph-title").html(OW.getLanguageText('spodpublic', 'graph_panel'));
            selected_graph = null;
        }else{
            if(selected_graph == null) commentGraphShow();
        }
        refreshOpenedDatalets();
    });
};

dataletGraphShow = function(){
    selected_graph = "datalet";
    $.post( SPODPUBLICROOM.get_graph_url,
        {
            id : SPODPUBLICROOM.public_room_id,
            type : "datalets"
        },
        function(data, status){
            data = JSON.parse(data);
            if(data.status == "ok"){
                $("#graph_content").html("<graph-datalet id='dgraph' width='"  + (window.innerWidth / 2) +
                                                      "' height='" + (window.innerHeight)    + "'></graph-datalet>");
                                                      //"' graph='"  + data.graph + "'></graph-datalet>");
                var g = document.getElementById('dgraph');
                g.graph = data.graph;
                g.init();

                $("#toolbar-graph-title").html(OW.getLanguageText('spodpublic', 'datalets_graph'));
                $("#datalet_graph").css('border-bottom-style','solid');
                $("#comment_graph").css('border-bottom-style','none');
                $("#user_graph").css('border-bottom-style','none');
                $("#opinion_graph").css('border-bottom-style','none');
            }
        }
    );
};

commentGraphShow = function(){
    selected_graph = "comment";
    $.post( SPODPUBLICROOM.get_graph_url,
        {
            id : SPODPUBLICROOM.public_room_id,
            type : "comments"
        },
        function(data, status){
            //data.replace(new RegExp("'","g"),"&#39;");
            //data.replace(new RegExp('"',"g"),"&#34;");
            data = JSON.parse(data);
            if(data.status == "ok"){
                $("#graph_content").html("<graph-datalet id='cgraph' width='"  + (window.innerWidth / 2) +
                                                      "' height='" + (window.innerHeight) + "'></graph-datalet>");
                                                     // "' graph='" + JSON.stringify(data.graph) + "'></graph-datalet>");

                var g = document.getElementById('cgraph');
                g.graph = data.graph;
                g.init();

                $("#toolbar-graph-title").html(OW.getLanguageText('spodpublic', 'comments_graph'));
                $("#comment_graph").css('border-bottom-style','solid');
                $("#datalet_graph").css('border-bottom-style','none');
                $("#user_graph").css('border-bottom-style','none');
                $("#opinion_graph").css('border-bottom-style','none');
            }
        }
    );
};

usersGraphShow = function(){
    selected_graph = "users";
    $.post( SPODPUBLICROOM.get_graph_url,
        {
            id : SPODPUBLICROOM.public_room_id,
            type : "users"
        },
        function(data, status){
            data = JSON.parse(data);
            if(data.status == "ok"){
                $("#graph_content").html("<graph-datalet id='ugraph' width='"+ (window.innerWidth / 2) +
                                                      "' height='"+ (window.innerHeight)   + "'></graph-datalet>");
                                                      //"' graph='" + data.graph + "'></graph-datalet>");
                var g = document.getElementById('ugraph');
                g.graph = data.graph;
                g.init();

                $("#toolbar-graph-title").html(OW.getLanguageText('spodpublic', 'users_graph'));
                $("#user_graph").css('border-bottom-style','solid');
                $("#comment_graph").css('border-bottom-style','none');
                $("#datalet_graph").css('border-bottom-style','none');
                $("#opinion_graph").css('border-bottom-style','none');
            }
        }
    );
};

opinionsGraphShow = function(){
    selected_graph = "opinions";
    $.post( SPODPUBLICROOM.get_graph_url,
        {
            id : SPODPUBLICROOM.public_room_id,
            type : "comments"
        },
        function(data, status){
            data = JSON.parse(data);
            if(data.status == "ok"){
                $("#graph_content").html("<graph-with-clustering-datalet id='ograph' width='"+ (window.innerWidth / 2) +
                                                                      "' height='"+ (window.innerHeight)   + "'></graph-with-clustering-datalet>");
                                                                      //"' graph='" + data.graph + "'></graph-with-clustering-datalet>");
                var g = document.getElementById('ograph');
                g.graph = data.graph;
                g.buildGraph();

                $("#toolbar-graph-title").html(OW.getLanguageText('spodpublic', 'opinions_graph'));
                $("#opinion_graph").css('border-bottom-style','solid');
                $("#user_graph").css('border-bottom-style','none');
                $("#comment_graph").css('border-bottom-style','none');
                $("#datalet_graph").css('border-bottom-style','none');
            }
        }
    );
};

selectGraph = function() {
    switch(selected_graph){
        case "comment":
            commentGraphShow();
            break;
        case "datalet":
            dataletGraphShow();
            break;
        case "users":
            usersGraphShow();
            break;
        case "opinions":
            opinionsGraphShow();
            break;
    }
};

$(document).ready(function () {
    $('#topic_container').perfectScrollbar();
    $('#graph_content').perfectScrollbar();

    //Scroll to bottom
    $("#topic_container").scrollTop( $( "#topic_container" ).prop( "scrollHeight" ) );

    OW.bind('base.comment_added', function(e){
       selectGraph();
    });

    OW.bind('base.comment_delete', function(e){
        selectGraph();
    });

    $(".sentiment-button").live("click", function()
    {
        var id = $(this).attr('id');
        switch($(this).attr('icon')){
            case "face":
                $(this).attr('icon', 'social:mood');
                $(this).attr('sentiment', '2');
                break;
            case "social:mood":
                $(this).attr('icon', 'social:mood-bad');
                $(this).attr('sentiment', '3');
                break;
            case "social:mood-bad":
                $(this).attr('icon', 'face');
                $(this).attr('sentiment', '1');
                break;
        }
    });

    $(".ow_comments_item_info").live("mouseover", function(){
        try {
            if (selected_graph != "opinions")
                document.querySelector('graph-datalet').fire("graph-datalet_on-node-hover", {"id": $(this).attr("id").split("_")[1]});
            else
                document.querySelector('graph-with-clustering-datalet').fire("graph-datalet_on-node-hover", {"id": $(this).attr("id").split("_")[1]});
        }catch(e){}
    });

    $(".ow_comments_item_info").live("mouseout", function(){
        try {
            if (selected_graph != "opinions")
                document.querySelector('graph-datalet').fire("graph-datalet_on-node-out", {"id": $(this).attr("id").split("_")[1]});
            else
                document.querySelector('graph-with-clustering-datalet').fire("graph-datalet_on-node-out", {"id": $(this).attr("id").split("_")[1]});
        }catch(e){}
    });
});

$(window).load(function() {
    //commentGraphShow();
    slideGraphPanel();
});