{if $mimeType === 'text/plain'}
    {capture assign='content'}{lang}wcf.user.invite.mail.plaintext{/lang}{/capture}
    {include file='email_plaintext'}
{else}
    {capture assign='content'}
        {lang}wcf.user.invite.mail.html{/lang}
    {/capture}
    {include file='email_html'}
{/if}
