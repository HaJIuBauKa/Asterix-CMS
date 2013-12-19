{addjs val="
    http://src.opendev.ru/3.0/codemirror-3.19/lib/codemirror.js
    http://src.opendev.ru/3.0/codemirror-3.19/mode/xml/xml.js
    http://src.opendev.ru/3.0/codemirror-3.19/mode/css/css.js
    http://src.opendev.ru/3.0/codemirror-3.19/mode/javascript/javascript.js
    http://src.opendev.ru/3.0/codemirror-3.19/mode/htmlmixed/htmlmixed.js
    /j/j.js
    http://src.opendev.ru/v4/j/acms_dev.js
"}{addcss val="
   http://src.opendev.ru/3.0/codemirror-3.19/lib/codemirror.css
   http://src.opendev.ru/v4/c/dev_styles.css
"}{include file="`$paths.admin_templates`/head.tpl" head_add=$head_add }
<body style="margin-top: 50px;">

<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/dev.html">Acterix CMS</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li{if !$ask->mode.0} class="active"{/if}><a href="/dev.html">Панель разработки</a></li>
<!--
                <li{if $ask->mode.0 == 'content'} class="active"{/if}><a href="/dev.content.html">Контент</a></li>
-->
                <li{if $ask->mode.0 == 'settings'} class="active"{/if}><a href="/dev.settings.html">Настройки</a></li>
                <li{if $ask->mode.0 == 'modules'} class="active"{/if}><a href="/dev.modules.html">Модули</a></li>
                <li{if $ask->mode.0 == 'templates'} class="active"{/if}><a href="/dev.templates.html">Шаблоны</a></li>
                <li{if $ask->mode.0 == 'styles'} class="active"{/if}><a href="/dev.styles.html">Стили</a></li>
                <li{if $ask->mode.0 == 'js'} class="active"{/if}><a href="/dev.js.html">JavaScript</a></li>
<!--
                <li{if $ask->mode.0 == 'images'} class="active"{/if}><a href="/dev.images.html">Картинки</a></li>
                <li{if $ask->mode.0 == 'files'} class="active"{/if}><a href="/dev.files.html">Файлы</a></li>
-->
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div>

<div class="container">

    <div class="row" style="margin-top: 20px;">

        {include file="`$paths.admin_templates`/dev/bar_left.tpl"}
        {include file="`$paths.admin_templates`/dev/bar_content.tpl"}
        {include file="`$paths.admin_templates`/dev/bar_right.tpl"}

    </div>

</div><!-- /.container -->

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width:1200px; width:80%;">
        <div class="modal-content" style="background-color: rgb(255, 244, 219);">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title acms-dev-js__modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body acms-dev-js__modal-content">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</body>
</html>
