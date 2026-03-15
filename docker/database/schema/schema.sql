CREATE TABLE `person`(
    `id` INT AUTO_INCREMENT NOT NULL,
    `public_id` VARCHAR(26) NOT NULL,
    `firstname` VARCHAR(150) NOT NULL,
    `lastname` VARCHAR(150) NOT NULL,
    `email` VARCHAR(180) NOT NULL,
    UNIQUE INDEX `uniq_person_public_id` (`public_id`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE `club` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `public_id` VARCHAR(26) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    UNIQUE INDEX `uniq_club_public_id` (`public_id`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE `person_relationship` (
    `id` INT AUTO_INCREMENT NOT NULL,
    `public_id` VARCHAR(26) NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    `is_emergency_contact` TINYINT DEFAULT 0 NOT NULL,
    `subject_id` INT NOT NULL,
    `related_person_id` INT NOT NULL,
    UNIQUE INDEX `uniq_person_relationship_public_id` (`public_id`),
    INDEX `idx_person_relationship_subject` (`subject_id`),
    INDEX `idx_person_relationship_related` (`related_person_id`),
    UNIQUE INDEX uniq_person_relationship (`subject_id`, `related_person_id`, `type`),
    PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;
ALTER TABLE `person_relationship` ADD CONSTRAINT `fk_person_relationship_subject_id` FOREIGN KEY (`subject_id`) REFERENCES `person` (`id`) ON DELETE CASCADE;
ALTER TABLE `person_relationship` ADD CONSTRAINT `fk_person_relationship_related_person_id` FOREIGN KEY (`related_person_id`) REFERENCES person (`id`) ON DELETE CASCADE;
