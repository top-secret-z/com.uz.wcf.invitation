<ul class="sidebarItemList">
    {foreach from=$userProfiles item=userProfile}
        <li class="box32">
            <a href="{link controller='User' object=$userProfile}{/link}" aria-hidden="true">{@$userProfile->getAvatar()->getImageTag(32)}</a>

            <div class="sidebarItemTitle">
                <h3>{user object=$userProfile}</h3>
                <small>{lang}wcf.user.invite.box.inviteSuccess{/lang}</small>
            </div>
        </li>
    {/foreach}
</ul>
