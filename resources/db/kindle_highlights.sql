CREATE DATABASE kindle_highlights;

CREATE  TABLE `kindle_highlights`.`new_table` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `book_id` INT(4) NULL ,
  `high_lighted_text` TEXT NULL ,
  `twitter` INT(1) NULL ,
  `facebook` INT(1) NULL ,
  PRIMARY KEY (`id`) );
	
CREATE  TABLE `kindle_highlights`.`kindle_books` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `title` TEXT NULL ,
  `author` VARCHAR(254) NULL ,
  `link` VARCHAR(254) NULL ,
  PRIMARY KEY (`id`) );