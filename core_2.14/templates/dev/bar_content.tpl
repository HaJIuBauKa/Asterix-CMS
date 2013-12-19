<div class="col-lg-{$center.size}">

    <div class="panel panel-default">
    {if $center.title}
        <div class="panel-heading">
            <h3 class="panel-title">{$center.title}</h3>
        </div>
    {/if}
        <div class="panel-body" style="height: 100%;">
        {if $center.iframe}
            <iframe name="content_frame" style="width: 100%; height: 100%; border: 0;" src="{$center.iframe}"></iframe>
        {/if}

        {if $center.template}
            {include file="`$paths.admin_templates`/dev/`$center.template`" content=$center}
        {/if}
        </div>
    </div>


</div>
