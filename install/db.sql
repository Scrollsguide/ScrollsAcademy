CREATE DATABASE IF NOT EXISTS `academy` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `academy`;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

INSERT INTO `categories` (`id`, `name`) VALUES
  (1, 'Video'),
  (2, 'Strategy'),
  (3, 'Decks'),
  (4, 'Judgement'),
  (5, 'UI'),
  (6, 'Beginner'),
  (7, 'Intermediate'),
  (8, 'Master');

CREATE TABLE IF NOT EXISTS `guidecategories` (
  `guideid` int(6) NOT NULL,
  `categoryid` int(2) NOT NULL,
  PRIMARY KEY (`guideid`,`categoryid`),
  KEY `guidecategories_ibfk_2` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `guides` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `summary` varchar(120) NOT NULL,
  `synopsis` varchar(1000) NOT NULL,
  `content` text NOT NULL,
  `markdown` text NOT NULL,
  `date` int(10) NOT NULL,
  `author` varchar(30) NOT NULL,
  `url` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `banner` varchar(100) NOT NULL,
  `video` text NOT NULL,
  `discussion` varchar(256) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `homepageblocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `homepageid` int(11) NOT NULL,
  `guideids` varchar(45) DEFAULT NULL,
  `layout` varchar(45) DEFAULT NULL,
  `header` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `homepageid` (`homepageid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `homepages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

INSERT INTO `homepages` (`id`) VALUES
  (1);

CREATE TABLE IF NOT EXISTS `series` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `url` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL,
  `banner` varchar(100) NOT NULL,
  `summary` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `seriesguides` (
  `seriesid` int(6) NOT NULL,
  `guideid` int(6) NOT NULL,
  `order` int(2) NOT NULL,
  PRIMARY KEY (`seriesid`,`guideid`),
  KEY `seriesguides_ibfk_2` (`guideid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `guidecategories`
ADD CONSTRAINT `guidecategories_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `guidecategories_ibfk_1` FOREIGN KEY (`guideid`) REFERENCES `guides` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `homepageblocks`
ADD CONSTRAINT `homepageblocks_ibfk_1` FOREIGN KEY (`homepageid`) REFERENCES `homepages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `seriesguides`
ADD CONSTRAINT `seriesguides_ibfk_2` FOREIGN KEY (`guideid`) REFERENCES `guides` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `seriesguides_ibfk_1` FOREIGN KEY (`seriesid`) REFERENCES `series` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;