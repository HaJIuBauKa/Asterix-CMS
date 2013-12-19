{if $left.size > 0}
    <div class="col-lg-{$left.size}">

        {if $left.title}
            <h4>{$left.title}</h4>
        {/if}

        {if $left.recs}
            <ul class="unstyled">
                {foreach from=$left.recs item=rec}
                    <li><a href="{$rec.url}">{$rec.title}</a></li>
                {/foreach}
            </ul>
        {/if}

        {if $left.title2}
            <h4>{$left.title2}</h4>
        {/if}

        {if $left.recs2}
            <ul class="unstyled">
                {foreach from=$left.recs2 item=rec}
                    <li><a href="{$rec.url}">{$rec.title}</a></li>
                {/foreach}
            </ul>
        {/if}

        {if $left.titl3e}
            <h4>{$left.title3}</h4>
        {/if}

        {if $left.recs3}
            <ul class="unstyled">
                {foreach from=$left.recs3 item=rec}
                    <li><a href="{$rec.url}">{$rec.title}</a></li>
                {/foreach}
            </ul>
        {/if}

    </div>
{/if}
