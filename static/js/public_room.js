var selected_graph        = null;
var last_selected_element = null;

window.addEventListener('graph-datalet_node-clicked', function(e){
    if(last_selected_element != null){
        last_selected_element.css('border', 'none');
        last_selected_element.css('font-weight', 'normal');
    }

    if(e.detail.node.id == 0) return;

    var curr_element = $("#comment_" + e.detail.node.originalId);
    $(curr_element).css('border', '1px solid #000000');
    $(curr_element).css('font-weight', 'bold');

    $('div[id^="nc_"]').css('display', 'none');

    $('div[id^="datalet_placeholder_"]').css('display', 'none');
    $('div[id^="datalet_placeholder_' + e.detail.node.originalId + '"]' ).css('display', 'block');
    $('.show_datalet').css('background', '#2196F3');

    $(curr_element).parents('div[id^="nc_"]').css('display', 'block');

    $("#topic_container").scrollTop($(curr_element).offset().top - 50);

    last_selected_element = curr_element;
});

slideGraphPanel = function(){
    $('#graph_container').toggle('slide', {direction: 'right'}, 300, function(){
        $('#topic_container').css('width' , ($('#graph_container').css('display') == 'none') ? '100%' : '50%');
        $('#graphs_buttons_panel').toggle('slide', {direction: 'right'}, 300);
        if($('#graph_container').css('display') == 'none'){
            $("#toolbar-graph-title").html('Graph Panel');
            selected_graph = null;
        }else{
            if(selected_graph == null) commentGraphShow();
        }
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
                $("#graph_content").html("<graph-datalet width='"+ (window.innerWidth / 2) +"' height='"+ (window.innerHeight) +"' graph='" + data.graph + "'></graph-datalet>");
                $("#toolbar-graph-title").html('Datalets graph');
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
            data = JSON.parse(data);
            if(data.status == "ok"){
                $("#graph_content").html("<graph-datalet width='"+ (window.innerWidth / 2) +"' height='"+ (window.innerHeight) +"' graph='" + data.graph + "'></graph-datalet>");
                $("#toolbar-graph-title").html('Comments graph');
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
                $("#graph_content").html("<graph-datalet width='"+ (window.innerWidth / 2) +"' height='"+ (window.innerHeight) +"' graph='" + data.graph + "'></graph-datalet>");
                $("#toolbar-graph-title").html('Users graph');
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
                $("#graph_content").html("<graph-with-clustering-datalet width='"+ (window.innerWidth / 2) +"' height='"+ (window.innerHeight) +"' graph='" + data.graph + "'></graph-with-clustering-datalet>");
                $("#toolbar-graph-title").html('Opinions graph');
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
            case "thumbs-up-down":
                $(this).attr('icon', 'thumb-up');
                $(this).attr('sentiment', '2');
                break;
            case "thumb-up":
                $(this).attr('icon', 'thumb-down');
                $(this).attr('sentiment', '3');
                break;
            case "thumb-down":
                $(this).attr('icon', 'thumbs-up-down');
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
    ODE.public_room_is_active = true;
    //commentGraphShow();
    //slideGraphPanel();
});