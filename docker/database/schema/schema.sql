CREATE TABLE `person`
(
    `id`        INT AUTO_INCREMENT NOT NULL,
    `public_id` VARCHAR(26)        NOT NULL,
    `firstname` VARCHAR(150)       NOT NULL,
    `lastname`  VARCHAR(150)       NOT NULL,
    `email`     VARCHAR(180)       NOT NULL,
    UNIQUE INDEX `uniq_person_public_id` (`public_id`),
    UNIQUE INDEX `uniq_person_email` (`email`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE `club`
(
    `id`        INT AUTO_INCREMENT NOT NULL,
    `public_id` VARCHAR(26)        NOT NULL,
    `name`      VARCHAR(150)       NOT NULL,
    UNIQUE INDEX `uniq_club_public_id` (`public_id`),
    UNIQUE INDEX `uniq_club_name` (`name`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE `person_contact`
(
    `id`                   INT AUTO_INCREMENT NOT NULL,
    `public_id`            VARCHAR(26)        NOT NULL,
    `type`                 VARCHAR(255)       NOT NULL,
    `is_emergency_contact` TINYINT DEFAULT 0  NOT NULL,
    `person_id`            INT                NOT NULL,
    `contact_person_id`    INT                NOT NULL,
    UNIQUE INDEX `uniq_person_contact_public_id` (`public_id`),
    INDEX `idx_person_contact_person` (`person_id`),
    INDEX `idx_person_contact_person_type` (`person_id`, `type`),
    INDEX `idx_person_contact_contact_person` (`contact_person_id`),
    UNIQUE INDEX uniq_person_contact (`person_id`, `contact_person_id`, `type`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;
ALTER TABLE `person_contact`
    ADD CONSTRAINT `fk_person_contact_person_id` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE;
ALTER TABLE `person_contact`
    ADD CONSTRAINT `fk_person_contact_contact_person_id` FOREIGN KEY (`contact_person_id`) REFERENCES person (`id`) ON DELETE CASCADE;

CREATE TABLE `membership`
(
    `id`        INT AUTO_INCREMENT NOT NULL,
    `public_id` VARCHAR(26)        NOT NULL,
    `person_id` INT                NOT NULL,
    `club_id`   INT                NOT NULL,
    `joined_at` DATETIME           NOT NULL,
    `ended_at`  DATETIME DEFAULT NULL,
    INDEX `idx_membership_person` (`person_id`),
    INDEX `idx_membership_club` (`club_id`),
    INDEX `idx_membership_person_club_ended_at` (`person_id`, `club_id`, `ended_at`),
    UNIQUE INDEX `uniq_membership_public_id` (`public_id`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;


CREATE TABLE club_membership_group
(
    `id`          INT AUTO_INCREMENT NOT NULL,
    `public_id`   VARCHAR(26)        NOT NULL,
    `name`        VARCHAR(512)       NOT NULL,
    `description` LONGTEXT DEFAULT NULL,
    `club_id`     INT                NOT NULL,
    INDEX `idx_cmg_club` (`club_id`),
    UNIQUE INDEX `uniq_cmg_public_id` (`public_id`),
    UNIQUE INDEX `uniq_cmg_name_club_id` (`name`, `club_id`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE club_membership_group_membership
(
    `id`            INT AUTO_INCREMENT NOT NULL,
    `public_id`     VARCHAR(26)        NOT NULL,
    `notes`         LONGTEXT DEFAULT NULL,
    `group_id`      INT                NOT NULL,
    `membership_id` INT                NOT NULL,
    INDEX `idx_cmgm_membership` (`membership_id`),
    INDEX `idx_cmgm_group` (`group_id`),
    INDEX `idx_cmgm_membership_group` (`membership_id`, `group_id`),
    UNIQUE INDEX uniq_cmgm_public_id (public_id),
    UNIQUE INDEX uniq_cmgm_membership_group (membership_id, group_id),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;


CREATE TABLE `interclub_membership_group`
(
    `id`          INT AUTO_INCREMENT NOT NULL,
    `public_id`   VARCHAR(26)        NOT NULL,
    `name`        VARCHAR(512)       NOT NULL,
    `description` LONGTEXT DEFAULT NULL,
    UNIQUE INDEX `uniq_img_public_id` (`public_id`),
    UNIQUE INDEX `uniq_img_name` (`name`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;


CREATE TABLE interclub_membership_group_membership
(
    `id`            INT AUTO_INCREMENT NOT NULL,
    `public_id`     VARCHAR(26)        NOT NULL,
    `notes`         LONGTEXT DEFAULT NULL,
    `group_id`      INT                NOT NULL,
    `membership_id` INT                NOT NULL,
    INDEX `idx_imgm_membership` (`membership_id`),
    INDEX `idx_imgm_group` (`group_id`),
    INDEX `idx_imgm_membership_group` (`membership_id`, `group_id`),
    UNIQUE INDEX `uniq_imgm_public_id` (`public_id`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;



ALTER TABLE `membership`
    ADD CONSTRAINT `fk_membership_person_id` FOREIGN KEY (`person_id`) REFERENCES person (`id`);
ALTER TABLE `membership`
    ADD CONSTRAINT `fk_membership_club_id` FOREIGN KEY (`club_id`) REFERENCES club (`id`);
ALTER TABLE `interclub_membership_group_membership`
    ADD CONSTRAINT `fk_imgm_group_id` FOREIGN KEY (`group_id`) REFERENCES `interclub_membership_group` (`id`);
ALTER TABLE `interclub_membership_group_membership`
    ADD CONSTRAINT `fk_imgm_membership_id` FOREIGN KEY (`membership_id`) REFERENCES `membership` (`id`);
ALTER TABLE `club_membership_group_membership`
    ADD CONSTRAINT `fk_cmgm_group_id` FOREIGN KEY (`group_id`) REFERENCES `club_membership_group` (`id`);
ALTER TABLE `club_membership_group_membership`
    ADD CONSTRAINT `fk_cmgm_membership_id` FOREIGN KEY (`membership_id`) REFERENCES `membership` (`id`);
ALTER TABLE `club_membership_group`
    ADD CONSTRAINT `fk_cmg_club_id` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`);


