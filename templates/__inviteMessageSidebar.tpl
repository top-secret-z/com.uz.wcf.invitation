{if MODULE_INVITE && INVITE_DISPLAY_MESSAGE_SIDEBAR && $userProfile->invites}
    <dt><a href="{link controller='InviteAdd'}{/link}" class="jsTooltip" title="{lang}wcf.user.invite.add{/lang}">{lang}wcf.user.invite.invites{/lang}</a></dt>
    <dd>{#$userProfile->invites}</dd>
{/if}
