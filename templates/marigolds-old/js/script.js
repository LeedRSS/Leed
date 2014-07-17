var keyCode = new Array();

keyCode['shift'] = 16;
keyCode['ctrl'] = 17;
keyCode['enter'] = 13;
keyCode['l'] = 76;
keyCode['m'] = 77;
keyCode['s'] = 83;
keyCode['n'] = 78;
keyCode['v'] = 86;
keyCode['p'] = 80;
keyCode['k'] = 75;
keyCode['o'] = 79;
keyCode['h'] = 72;
keyCode['j'] = 74;
keyCode['space'] = 32;

$(document).ready(function(){

    // Page settings
    if($('.settings').length){

        // Si nom du bloc en hash dans url
        var hash=window.location.hash;
        if(hash.length){
            toggleBlocks(hash);
        }

        // Affichage des differents blocs apres clic sur le menu
        $('.toggle').click(function(){
                toggleBlocks($(this).attr("href"));
            }
        );

    }else{

        targetThisEvent($('article section:first'),true);
        addEventsButtonLuNonLus();

        // on initialise ajaxready à true au premier chargement de la fonction
        $(window).data('ajaxready', true);
        $('article').append('<div id="loader">'+_t('LOADING')+'</div>');
        $(window).data('page', 1);
        $(window).data('nblus', 0);

        if ($(window).scrollTop()==0) scrollInfini();
    }
    //alert(_t('IDENTIFIED_WITH',['idleman']));

    // focus sur l'input du login
    if (document.getElementById('inputlogin')) document.getElementById('inputlogin').focus();
});

function _t(key,args){
    value = i18n[key];
    if(args!=null){
        for(i=0;i<args.length;i++){
            value = value.replace('$'+(i+1),args[i]);
        }
    }
    return value;
}

$(document).keydown(function (e) {
    switch(true) {
        case e.altKey||e.ctrlKey||e.shiftKey||e.metaKey:
        case $('.index').length==0:
        case $("input:focus").length!=0:
            return true;
    }
    switch(e.which){

        case keyCode['m']:
            //marque l'élément sélectionné comme lu / non lu
            readTargetEvent();
            return false;
        break;

        case keyCode['l']:
            //marque l'élément precédent comme non lu et réafficher
            targetPreviousEventRead();
            return false;
        break;

        case keyCode['s']:
            //marque l'élément sélectionné comme favori / non favori
            switchFavoriteTargetEvent();
            return false;
        break;
        case keyCode['n']:
            //élément suivant (sans l'ouvrir)
            targetNextEvent();
            return false;
        break;
        case keyCode['v']:
            //ouvre l'url de l'élément sélectionné
            openTargetEvent();
            return false;
        break;
        case keyCode['p']:
            //élément précédent (sans l'ouvrir)
            targetPreviousEvent();
            return false;
        break;
        case keyCode['space']:
            //élément suivant (et l'ouvrir)
            targetNextEvent();
            openTargetEvent();
            return false;
        break;
        case keyCode['k']:
            //élément précédent (et l'ouvrir)
            targetPreviousEvent();
            openTargetEvent();
            return false;
        break;
        case keyCode['o']:
        case keyCode['enter']:
            //ouvrir l'élément sélectionné
            openTargetEvent();
            return false;
        break;
        case keyCode['h']:
            //ouvrir/fermer le panneau d'aide
            document.getElementById( 'helpPanel' ).style.display == 'block' ? document.getElementById( 'helpPanel' ).style.display = 'none' : document.getElementById( 'helpPanel' ).style.display = 'block';
            return false;
        break;
        case keyCode['j']:
            // Affiche / cache les blocs résumé / content
            toggleArticleDisplayMode(document.getElementById('btnDisplayMode_'+$('.eventSelected').attr('id')),$('.eventSelected').attr('id'));
            return false;
        break;
    }
});

$(window).scroll(function(){
    scrollInfini();
});


/** SECTION MARKET & PLUGINS **/

function togglePluginMenu(element,page){
    $(element).parent().find('li').removeClass('selected');
    $(element).addClass('selected');
    if(page=='market'){
        $('.marketZone').fadeIn(300);
        $('.installedZone').hide();
        $('#btnSearchPlugin').trigger("click");

    }else{
        $('.marketZone').hide();
        $('.installedZone').fadeIn(300);
    }
}

function searchPlugin(keyword){
    $('#resultsPlugin').html('Chargement en cours...');
    var baseUrl = (location.protocol == 'https:'?"https://market.idleman.fr:666":"http://market.idleman.fr")
    $.getJSON(baseUrl+"/api.php?s=leed&m=search&k="+keyword+"&callback=?");
}

function jsonp(data){
    
    switch(data.method){
        case 'search':
            $('#resultsPlugin').html('');
            if(data.results!=null && data.results.length>0){
                for(var key in data.results){
                    var plugin = data.results[key];
                    tpl = 
                    '<li>\
                        <ul>\
                            <li><h4>Nom: </h4>'+plugin.name+'</li>\
                            <li><h4>Auteur: </h4><a href="mailto:'+plugin.mail+'">'+plugin.author+'</a></li>\
                            <li><h4>Licence: </h4><a href="http://google.fr/#q='+plugin.licence+'">'+plugin.licence+'</a></li>\
                            <li><h4>Version: </h4><code>'+plugin.version+'</code></li>\
                            <li><h4>Site web: </h4><a href="'+plugin.link+'">'+plugin.link+'</a></li>\
                            <li>'+plugin.description+'</li>\
                            <li><button class="btn" onclick="installPlugin(\''+plugin.dll+'\');">Installer</button></li>\
                        </ul>\
                    </li>';
                    $('#resultsPlugin').append(tpl);
                }
            }else{
                $('#resultsPlugin').append('<li>Aucun résultats pour cette recherche.</li>');
            }   
        break;
        case 'get':
        
        break;
    }
}

function installPlugin(url){
    $('#resultsPlugin').load('action.php?action=installPlugin&zip='+encodeURIComponent(url));
}

/** FIN MARKET & PLUGINS **/


function scrollInfini() {
    var deviceAgent = navigator.userAgent.toLowerCase();
    var agentID = deviceAgent.match(/(iphone|ipod|ipad)/);

    if($('.index').length) {
        // On teste si ajaxready vaut false, auquel cas on stoppe la fonction
        if ($(window).data('ajaxready') == false) return;

        if(($(window).scrollTop() + $(window).height()) + 50 >= $(document).height()
           || agentID && ($(window).scrollTop() + $(window).height()) + 150 > $(document).height()
           || $('article').position().top + $('article').height() < $('aside').height()
           )
        {
            // lorsqu'on commence un traitement, on met ajaxready à false
            $(window).data('ajaxready', false);

             //j'affiche mon loader pour indiquer le chargement
            $('article #loader').show();

            //utilisé pour l'alternance des couleurs d'un article à l'autre
            if ($('article section:last').hasClass('eventHightLighted')) {
                hightlighted = 1;
            } else {
                hightlighted = 2;
            }

            // récupération des variables passées en Get
            var action = getUrlVars()['action'];
            var folder = getUrlVars()['folder'];
            var feed = getUrlVars()['feed'];
            var order = getUrlVars()['order'];
            if (order) {
                order = '&order='+order
            } else {
                order = ''
            }

            $.ajax({
                url: './article.php',
                type: 'post',
                data: 'scroll='+$(window).data('page')+'&nblus='+$(window).data('nblus')+'&hightlighted='+hightlighted+'&action='+action+'&folder='+folder+'&feed='+feed+order,

                //Succès de la requête
                success: function(data) {
                    if (data.replace(/^\s+/g,'').replace(/\s+$/g,'') != '')
                    {    // on les insère juste avant le loader
                        $('article #loader').before(data);
                        //on supprime de la page le script pour ne pas intéragir avec les next & prev
                        $('article .scriptaddbutton').remove();
                        //si l'élement courant est caché, selectionner le premier élément du scroll
                        //ou si le div loader est sélectionné (quand 0 article restant suite au raccourcis M)
                        if (($('article section.eventSelected').attr('style')=='display: none;')
                            || ($('article div.eventSelected').attr('id')=='loader'))
                        {
                            targetThisEvent($('article section.scroll:first'), true);
                        }
                        // on les affiche avec un fadeIn
                        $('article section.scroll').fadeIn(600);
                        // on supprime le tag de classe pour le prochain scroll
                        $('article section.scroll').removeClass('scroll');
                        $(window).data('ajaxready', true);
                        $(window).data('page', $(window).data('page')+1);
                        $(window).data('enCoursScroll',0);
                        // appel récursif tant qu'un scroll n'est pas detecté.
                        if ($(window).scrollTop()==0) scrollInfini();
                    } else {
                        $('article #loader').addClass('finScroll');
                    }
                 },
                complete: function(){
                    // le chargement est terminé, on fait disparaitre notre loader
                    $('article #loader').fadeOut(400);
                }
            });
        }
    }
};

/* Fonctions de séléctions */
/* Cette fonction sera utilisé pour le scroll infinie, afin d'ajouter les évènements necessaires */
function addEventsButtonLuNonLus(){
    var handler = function(event){
    var target = event.target;
    var id = this.id;
    if($(target).hasClass('readUnreadButton')){
        buttonAction(target,id);
    }else{
        targetThisEvent(this);
    }
    }
    // on vire tous les évènements afin de ne pas avoir des doublons d'évènements
    $('article section').unbind('click');
    // on bind proprement les click sur chaque section
    $('article section').bind('click', handler);
}

function targetPreviousEvent(){
    targetThisEvent($('.eventSelected').prevAll(':visible').first(),true);
}
function targetNextEvent(){

    targetThisEvent($('.eventSelected').nextAll(':visible').first(),true);
}

function targetThisEvent(event,focusOn){
    target = $(event);
    if(target.prop("tagName")=='SECTION'){
        $('.eventSelected').removeClass('eventSelected');
        target.addClass('eventSelected');
        var id = target.attr('id');
        if(id && focusOn)window.location = '#'+id;
    }
    if(target.prop("tagName")=='DIV'){
        $('.eventSelected').removeClass('eventSelected');
        target.addClass('eventSelected');
    }
    // on débloque les touches le plus tard possible afin de passer derrière l'appel ajax
}
function openTargetEvent(){
    window.open($('.eventSelected .articleTitle a').attr('href'), '_blank');
}

function readTargetEvent(){
    var buttonElement = $('.eventSelected .readUnreadButton');
    var id = $(target).attr('id');
    readThis(buttonElement,id,null,function(){
        // on fait un focus sur l'Event suivant
        targetThisEvent($('.eventSelected').nextAll(':visible').first(),true);
        $(window).scroll();
    });
}

function targetPreviousEventRead(){
    targetThisEvent($('.eventSelected').prev().css('display','block'),true);
    var buttonElement = $('.eventSelected .readUnreadButton');
    var id = $(target).attr('id');
    unReadThis(buttonElement,id,null);
}

function readAllDisplayedEvents(){
    $('article section').each(function(i,article){
        var buttonElement = $('.readUnreadButton',article);
        var id = $('.anchor',article).attr('id');
        readThis(buttonElement,id);
    });
}

function switchFavoriteTargetEvent(){
    var id = $(target).attr('id');
    if($('.favorite',target).html()=='Favoriser'){
        addFavorite($('.favorite',target),id);
    }else{
        removeFavorite($('.favorite',target),id);
    }
    // on débloque les touches le plus tard possible afin de passer derrière l'appel ajax
}

/* Fonctions de séléctions fin */

function toggleFolder(element,folder){
    feedBloc = $('ul',$(element).parent().parent());

    open = 0;
    if(feedBloc.css('display')=='none') open = 1;
    feedBloc.slideToggle(200);
    $(element).html((!open?_t('UNFOLD'):_t('FOLD')));
    $.ajax({
                  url: "./action.php?action=changeFolderState",
                  data:{id:folder,isopen:open}
    });
}

function addFavorite(element,id){
    var activeScreen = $('#pageTop').html();
    $.ajax({
        url: "./action.php?action=addFavorite",
        data:{id:id},
        success:function(msg){
            if(msg.status == 'noconnect') {
                alert(msg.texte)
            } else {
                if( console && console.log && msg!="" ) console.log(msg);
                $(element).attr('onclick','removeFavorite(this,'+id+');').html(_t('UNFAVORIZE'));
                // on compte combien d'article ont été remis en favoris sur la pages favoris (scroll infini)
                if (activeScreen=='favorites') {
                    $(window).data('nblus', $(window).data('nblus')-1);
                    addOrRemoveFeedNumber('+');
                }
            }
        }
    });
}

function removeFavorite(element,id){
    var activeScreen = $('#pageTop').html();
    $.ajax({
        url: "./action.php?action=removeFavorite",
        data:{id:id},
        success:function(msg){
            if(msg.status == 'noconnect') {
                alert(msg.texte)
            } else {
                if( console && console.log && msg!="" ) console.log(msg);
                $(element).attr('onclick','addFavorite(this,'+id+');').html(_t('FAVORIZE'));
                // on compte combien d'article ont été remis en favoris sur la pages favoris (scroll infini)
                if (activeScreen=='favorites') {
                    $(window).data('nblus', $(window).data('nblus')+1);
                    addOrRemoveFeedNumber('-');
                }
            }
        }
    });
}

function renameFolder(element,folder){
    var folderLine = $(element).parent();
    var folderNameCase = $('span',folderLine);
    var value = folderNameCase.html();
    $(element).html('Enregistrer');
    $(element).attr('style','background-color:#0C87C9;');
    $(element).attr('onclick','saveRenameFolder(this,'+folder+')');
    folderNameCase.replaceWith('<span><input type="text" name="folderName" value="'+value+'"/></span>');
}


function saveRenameFolder(element,folder){
    var folderLine = $(element).parent();
    var folderNameCase = $('span',folderLine);
    var value = $('input',folderNameCase).val();
    $(element).html(_t('RENAME'));
    $(element).attr('style','background-color:#F16529;');
    $(element).attr('onclick','renameFolder(this,'+folder+')');
    folderNameCase.replaceWith('<span>'+value+'</span>');
    $.ajax({
        url: "./action.php?action=renameFolder",
        data:{id:folder,name:value}
    });
}


function renameFeed(element,feed){
    var feedLine = $(element).parent().parent();
    var feedNameCase = feedLine.children('.js-feedTitle').children('a:nth-child(1)');
    var feedNameValue = feedNameCase.html();
    var feedUrlCase = feedLine.children('.js-feedTitle').children('a:nth-child(2)');
    var feedUrlValue = feedUrlCase.attr('href');
    var url = feedNameCase.attr('href');
    $(element).html(_t('SAVE'));
    $(element).attr('style','background-color:#0C87C9;');
    $(element).attr('onclick','saveRenameFeed(this,'+feed+',"'+url+'")');
    feedNameCase.replaceWith('<input type="text" name="feedName" value="'+feedNameValue+'" size="25" />');
    feedUrlCase.replaceWith('<input type="text" name="feedUrl" value="'+feedUrlValue+'" size="25" />');
}

function saveRenameFeed(element,feed,url){
    var feedLine = $(element).parent().parent();
    var feedNameCase = feedLine.children('.js-feedTitle:first').children('input[name="feedName"]');
    var feedNameValue = feedNameCase.val();
    var feedUrlCase = feedLine.children('.js-feedTitle:first').children('input[name="feedUrl"]');
    var feedUrlValue = feedUrlCase.val();
    $(element).html('Renommer');
    $(element).attr('style','background-color:#F16529;');
    $(element).attr('onclick','renameFeed(this,'+feed+')');
    feedNameCase.replaceWith('<a href="'+url+'">'+feedNameValue+'</a>');
    feedUrlCase.replaceWith('<a class="underlink" href="'+feedUrlValue+'">'+feedUrlValue+'</a>');
    $.ajax({
        url: "./action.php?action=renameFeed",
        data:{id:feed,name:feedNameValue,url:feedUrlValue}
    });
}


function changeFeedFolder(element,id){
    var value = $(element).val();
    window.location = "./action.php?action=changeFeedFolder&feed="+id+"&folder="+value;
}


function readThis(element,id,from,callback){
    var activeScreen = $('#pageTop').html();
    var parent = $(element).parent().parent();
    var nextEvent = $('#'+id).nextAll(':visible').first();
    //sur les éléments non lus
    if(!parent.hasClass('eventRead')){
        addOrRemoveFeedNumber('-');
        $.ajax({
            url: "./action.php?action=readContent",
            data:{id:id},
            success:function(msg){
                if(msg.status == 'noconnect') {
                    alert(msg.texte)
                } else {
                    if( console && console.log && msg!="" ) console.log(msg);
                    switch (activeScreen){
                        case '':
                            // cas de la page d'accueil
                            parent.addClass('eventRead');
                            parent.fadeOut(200,function(){
                                if(callback){
                                    callback();
                                }else{
                                    targetThisEvent(nextEvent,true);
                                }
                                // on simule un scroll si tous les events sont cachés
                                if($('article section:last').attr('style')=='display: none;') {
                                    $(window).scrollTop($(document).height());
                                }
                            });
                            // on compte combien d'article ont été lus afin de les soustraires de la requête pour le scroll infini
                            $(window).data('nblus', $(window).data('nblus')+1);
                        break;
                        case 'selectedFolder':
                        case 'selectedFeed':
                            parent.addClass('eventRead');
                            if(callback){
                                callback();
                            }else{
                                targetThisEvent(nextEvent,true);
                            }
                            // on compte combien d'article ont été lus afin de les soustraires de la requête pour le scroll infini
                            $(window).data('nblus', $(window).data('nblus')+1);
                        break;
                        default:
                            // autres cas : favoris, selectedFeed ...
                            parent.addClass('eventRead');
                            if(callback){
                                callback();
                            }else{
                                targetThisEvent(nextEvent,true);
                            }
                        break;
                    }
                }
            }
        });
    }else{  // sur les éléments lus
            // si ce n'est pas un clic sur le titre de l'event
        if(from!='title'){
            addOrRemoveFeedNumber('+');
            $.ajax({
                    url: "./action.php?action=unreadContent",
                    data:{id:id},
                    success:function(msg){
                        if(msg.status == 'noconnect') {
                            alert(msg.texte)
                        } else {
                            if( console && console.log && msg!="" ) console.log(msg);
                            parent.removeClass('eventRead');
                            // on compte combien d'article ont été remis à non lus
                            if ((activeScreen=='') || (activeScreen=='selectedFolder')|| (activeScreen=='selectedFeed'))
                                $(window).data('nblus', $(window).data('nblus')-1);
                            if(callback){
                                callback();
                            }
                        }
                    }
            });
        }
    }

}

function unReadThis(element,id,from){
    var activeScreen = $('#pageTop').html();
    var parent = $(element).parent().parent();
    if(parent.hasClass('eventRead')){
        if(from!='title'){
            $.ajax({
                url: "./action.php?action=unreadContent",
                data:{id:id},
                success:function(msg){
                    if(msg.status == 'noconnect') {
                        alert(msg.texte)
                    } else {
                        if( console && console.log && msg!="" ) console.log(msg);
                        parent.removeClass('eventRead');
                        // on compte combien d'article ont été remis à non lus
                        if ((activeScreen=='') || (activeScreen=='selectedFolder')|| (activeScreen=='selectedFeed'))
                            $(window).data('nblus', $(window).data('nblus')-1);

                        addOrRemoveFeedNumber('+');
                    }
                }
            });
        }
    }

}

//synchronisation manuelle lancée depuis le boutton du menu
function synchronize(code){
    if(code!=''){
        $('article').prepend('<section>'+
        '<iframe class="importFrame" src="action.php?action=synchronize&format=html&code='+code+'" name="idFrameSynchro" id="idFrameSynchro" width="100%" height="300" ></iframe>'+
        '</section>');
    }else{
        alert(_t('YOU_MUST_BE_CONNECTED_FEED'));
    }
}

// Affiche / cache les blocs résumé / content
function toggleArticleDisplayMode(button, target){
    if ($('#'+target+' > .summary').length>0 && $('#'+target+' > .summary').attr('style')!='display: none;'){

        // je suis en mode affichage réduit et je passe en affichage mode complet
        action = 'content';
        $('#'+target+' > .summary').hide();
        // chargement de l'article complet (content)
        if ($.trim($('#'+target+' > .content').text()).length==0){
            $.ajax({
                url: "./action.php?action=articleDisplayMode&articleDisplayMode="+action+'&event_id='+target,
                success:function(msg){
                    if(msg.status == 'noconnect') {
                        alert(msg.texte)
                    } else {
                        if( console && console.log && msg!="" ) console.log(msg);
                        $('#'+target+' > .content').html(msg);
                        $('#'+target+' > .content').show()
                        // btn pour passer en mode title
                        button.innerHTML = '|||';
                        button.title = _t('EVENT_DISPLAY_CONTENT');
                        $('#'+target+' > .articleDetails').last().show();
                    }
                }
            });
        } else {
            $('#'+target+' > .content').show()
            // btn pour passer en mode title
            button.innerHTML = '|||';
            button.title = _t('EVENT_DISPLAY_CONTENT');
            $('#'+target+' > .articleDetails').last().show();
        }

    }else{
        if ($('#'+target+' > .content').length>0 && $('#'+target+' > .content').attr('style')!='display: none;'){
            // je suis en mode affichage complet et je passe en affichage mode title
            $('#'+target+' > .content').hide();
            // btn pour passer en mode reduit
            button.innerHTML = '|&nbsp;&nbsp;';
            button.title = _t('EVENT_DISPLAY_TITLE');
            if ($('#'+target+' > .articleDetails').length > 1) {
                $('#'+target+' > .articleDetails').last().hide();
            }

        }  else {

            // je suis en mode affichage titre et je passe en affichage mode réduit
            action = 'summary';
            // chargement de l'article réduit (description)
            if ($.trim($('#'+target+' > .summary').text()).length==0){
                $.ajax({
                    url: "./action.php?action=articleDisplayMode&articleDisplayMode="+action+'&event_id='+target,
                    success:function(msg){
                        if(msg.status == 'noconnect') {
                            alert(msg.texte)
                        } else {
                            if( console && console.log && msg!="" ) console.log(msg);
                            $('#'+target+' > .summary').html(msg);
                            $('#'+target+' > .summary').show();
                            // btn pour passer en mode complet
                            button.innerHTML = '||&nbsp;';
                            button.title = _t('EVENT_DISPLAY_SUMMARY');
                        }
                    }
                });
            } else {
                $('#'+target+' > .summary').show();
                // btn pour passer en mode complet
                button.innerHTML = '||&nbsp;';
                button.title = _t('EVENT_DISPLAY_SUMMARY');
            }
        }
    }
}

// Disparition block et affichage block clique
function toggleBlocks(target){
    target=target.substring(1);
    $('#main article > section').hide();$('.'+target).fadeToggle(200);
}

// affiche ou cache les feeds n'ayant pas d'article non lus.
function toggleUnreadFeedFolder(button,action){
    $.ajax({
        url: "./action.php?action=displayOnlyUnreadFeedFolder&displayOnlyUnreadFeedFolder="+action,
        success:function(msg){
            if(msg.status == 'noconnect') {
                alert(msg.texte)
            } else {
                if( console && console.log && msg!="" ) console.log(msg);
                //Afficher ou cacher les feeds
                if(action){
                    $('.hidefeed').hide();
                }else{
                    $('.hidefeed').show();
                }
                //changement de l'évènement onclick pour faire l'inverse lors du prochain clic
                $(button).attr('onclick','toggleUnreadFeedFolder(this,'+!action+');');

            }
        }
    });
}

function buttonAction(target,id){
    // Check unreadEvent
    if($('#pageTop').html()){
        var from=true;
    }else{
        var from='';
    }
    readThis(target,id,from);
}


// permet de récupérer les variables passée en get dans l'URL et des les parser
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        if (hash[1]){
            rehash = hash[1].split('#');
            vars[hash[0]] = rehash[0];
        } else {
            vars[hash[0]] = '';
        }


    }
    return vars;
}

// affiche ou cache les feeds n'ayant pas d'article non lus.
function toggleFeedVerbose(button,action,idFeed){
    $.ajax({
        url: "./action.php?action=displayFeedIsVerbose&displayFeedIsVerbose="+action+"&idFeed="+idFeed,
        success:function(msg){
            if(msg.status == 'noconnect') {
                alert(msg.texte)
            } else {
                if( console && console.log && msg!="" ) console.log(msg);
                //changement de l'évènement onclick pour faire l'inverse lors du prochain clic
                var reverseaction = 0
                if (action==0) { reverseaction = 1 }
                $(button).attr('onclick','toggleFeedVerbose(this,'+reverseaction+', '+idFeed+');');
            }
        }
    });
}

// Bouton permettant l'affichage des options d'affichage et de non affichage des flux souhaités en page d'accueil
function toggleOptionFeedVerbose(button,action){
    $.ajax({
        url: "./action.php?action=optionFeedIsVerbose&optionFeedIsVerbose="+action,
        success:function(msg){
            if(msg.status == 'noconnect') {
                alert(msg.texte)
            } else {
                if( console && console.log && msg!="" ) console.log(msg);
                //changement de l'évènement onclick pour faire l'inverse lors du prochain clic
                var reverseaction = 0
                if (action==0) { reverseaction = 1 }
                $(button).attr('onclick','toggleOptionFeedVerbose(this,'+reverseaction+');');
                //Changement du statut des cases à cocher sur les feed (afficher ou cacher)
                if (action==1){
                    $('.feedVerbose').hide();
                }else{
                    $('.feedVerbose').show();
                }
            }
        }
    });
}

// fonction d'ajout ou de retrait d'un article dans les compteurs
// operator = '-' pour les soustraction '+' pour les ajouts
function addOrRemoveFeedNumber(operator){
    if (operator == '-') {
        // on diminue le nombre d'article en haut de page
        var nb = parseInt($('#nbarticle').html()) - 1;
        if (nb > 0) {
            $('#nbarticle').html(nb);
        } else {
            $('#nbarticle').html(0);
        }
        // on diminue le nombre sur le flux en question
        var feed_id = ($('.eventSelected').eq(0).data('feed'));
        var feed = $('#menuBar ul a[href$="feed=' + feed_id + '"]').next().find('span');
        nb = parseInt($(feed).text()) - 1;
        if (nb > 0) {
            $(feed).text(nb);
        } else {
            $(feed).text(0);
        }
        // on diminue le nombre sur le dossier
        var feed_folder = ($(feed).closest('ul').prev('h1').find('.unreadForFolder'));
        if(isNaN(feed_folder.html())) {
            var regex='[0-9]+';
            var found = feed_folder.html().match(regex);
            nb = parseInt(found[0])-1;
            var regex2='[^0-9]+';
            var lib = feed_folder.html().match(regex2);
            if (nb > 0) {
                feed_folder.html(nb +lib[0])
            } else {
                feed_folder.html('0' +lib[0])
            }
        }
    } else {
        // on augmente le nombre d'article en haut de page
        var nb = parseInt($('#nbarticle').html()) + 1;
        $('#nbarticle').html(nb);
        // on augmente le nombre sur le flux en question
        var feed_id = ($('.eventSelected').eq(0).data('feed'));
        var feed = $('#menuBar ul a[href$="feed=' + feed_id + '"]').next().find('span');
        nb = parseInt($(feed).text()) + 1;
        $(feed).text(nb);
        // on augmente le nombre sur le dossier
        var feed_folder = ($(feed).closest('ul').prev('h1').find('.unreadForFolder'));
        if(isNaN(feed_folder.html())) {
            var regex='[0-9]+';
            var found = feed_folder.html().match(regex);
            nb = parseInt(found[0])+1;
            var regex2='[^0-9]+';
            var lib = feed_folder.html().match(regex2);
            if (nb > 0) {
                feed_folder.html(nb +lib[0])
            } else {
                feed_folder.html('0' +lib[0])
            }
        }
    }
}
