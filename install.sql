-- Add columns in user table
ALTER TABLE wcf1_user ADD invites INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user ADD inviteSuccess INT(10) NOT NULL DEFAULT 0;

-- Invitation
DROP TABLE IF EXISTS wcf1_user_invite;
CREATE TABLE wcf1_user_invite (
    inviteID            INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code                VARCHAR(100) NOT NULL DEFAULT '',
    codeExpires            INT(10) NOT NULL DEFAULT 0,
    emails                TEXT NOT NULL,
    inviterID            INT(10) DEFAULT NULL,
    inviterName            VARCHAR(255) NOT NULL DEFAULT '',
    message                TEXT NOT NULL,
    subject                VARCHAR(255) NOT NULL DEFAULT '',
    successCount        INT(10) NOT NULL DEFAULT 0,
    time                INT(10) NOT NULL DEFAULT 0,
    additionalData        MEDIUMTEXT,

    KEY inviterID (inviterID)
);

DROP TABLE IF EXISTS wcf1_user_invite_code;
CREATE TABLE wcf1_user_invite_code (
    code                VARCHAR(100) NOT NULL DEFAULT '',
    used                INT(10) NOT NULL DEFAULT 0,

    UNIQUE KEY code (code)
);

DROP TABLE IF EXISTS wcf1_user_invite_email;
CREATE TABLE wcf1_user_invite_email (
    emailID                INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email                VARCHAR(255) NOT NULL DEFAULT '',
    inviteID            INT(10) NOT NULL DEFAULT 0,
    time                INT(10) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS wcf1_user_invite_success;
CREATE TABLE wcf1_user_invite_success (
    successID            INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    inviteID            INT(10),
    inviterID            INT(10),
    inviterName            VARCHAR(255) NOT NULL DEFAULT '',
    userID                INT(10),
    username            VARCHAR(255) NOT NULL DEFAULT '',
    time                INT(10) NOT NULL DEFAULT 0
);

ALTER TABLE wcf1_user_invite ADD FOREIGN KEY (inviterID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_user_invite_success ADD FOREIGN KEY (inviteID) REFERENCES wcf1_user_invite (inviteID) ON DELETE CASCADE;
ALTER TABLE wcf1_user_invite_success ADD FOREIGN KEY (inviterID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wcf1_user_invite_success ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
