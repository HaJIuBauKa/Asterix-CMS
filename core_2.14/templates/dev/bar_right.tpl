{if $right.size > 0}
    <div class="col-lg-{$right.size}">

        <h4>{$right.title}</h4>

        <div class="panel-group" id="accordion">
        {foreach from=$right.recs item=dir key=key}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse{$key}" class="collapsed">
                            {$dir.title}
                        </a>
                    </h4>
                </div>
                <div id="collapse{$key}" class="panel-collapse collapse" style="height: 0px;">
                    <div class="panel-body">

                        <ol>
                        {foreach $dir.recs item=rec key=key2}
                            <li><a href="#" data-help="{$key}.{$key2}" data-dir="{$ask->tree.0.mode.0}" class="acms-dev-js__help">{$rec.title}</a></li>
                        {/foreach}
                        </ol>

                    </div>
                </div>
            </div>
        {/foreach}
        </div>

    </div>
{/if}
