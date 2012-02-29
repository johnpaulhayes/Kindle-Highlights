CREATE  TABLE `kindle_highlights`.`new_table` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `book_id` INT(4) NULL ,
  `high_lighted_text` TEXT NULL ,
  `tweeted` INT(1) NULL ,
  PRIMARY KEY (`id`) );
	
CREATE  TABLE `kindle_highlights`.`kindle_books` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `title` TEXT NULL ,
  `author` VARCHAR(254) NULL ,
  `isbn` VARCHAR(15) NULL ,
  PRIMARY KEY (`id`) );
	
	
pseudo code.

populate form variables
input field email,
checkbox named create
submit via post to 
https://www.amazon.com/ap/signin
There are others, but for the minute lets see if these work

PHP

