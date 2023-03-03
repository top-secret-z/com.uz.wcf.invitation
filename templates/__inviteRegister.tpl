{if MODULE_INVITE}
    {if $inviteCodeOption == 'must'}
        <dl{if $errorType.inviteCode|isset} class="formError"{/if}>
            <dt>
                <label for="inviteCode">{lang}wcf.user.invite.inviteCode{/lang}</label> <span class="customOptionRequired">*</span>
            </dt>
            <dd>
                <input type="text" id="inviteCode" name="inviteCode" value="{$inviteCode}"  class="medium">
                {if $errorType.inviteCode|isset}
                    <small class="innerError">
                        {lang}wcf.user.invite.inviteCode.error.{$errorType.inviteCode}{/lang}
                    </small>
                {/if}
                <small>{lang}wcf.user.invite.inviteCode.description.must{/lang}</small>
            </dd>
        </dl>
    {/if}

    {if $inviteCodeOption == 'may'}
        <dl{if $errorType.inviteCode|isset} class="formError"{/if}>
            <dt>
                <label for="inviteCode">{lang}wcf.user.invite.inviteCode{if INVITE_CODE_USERNAME}.username{/if}{/lang}</label>
            </dt>
            <dd>
                <input type="text" id="inviteCode" name="inviteCode" value="{$inviteCode}"  class="medium">
                {if $errorType.inviteCode|isset}
                    <small class="innerError">
                        {lang}wcf.user.invite.inviteCode{if INVITE_CODE_USERNAME}.username{/if}.error.{$errorType.inviteCode}{/lang}
                    </small>
                {/if}
                <small>{lang}wcf.user.invite.inviteCode{if INVITE_CODE_USERNAME}.username{/if}.description.may{/lang}</small>
            </dd>
        </dl>
    {/if}
{/if}
