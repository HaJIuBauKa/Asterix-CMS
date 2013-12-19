[{$ask->module}]
{preload module=start data=recs dir=start result=recs}
<ol>
{foreach from=$recs item=rec}
    <li>
        <a href="/dev.content{$rec.url_clear}.editRecord.html">{$rec.title}</a>
    </li>
{/foreach}
</ol>

