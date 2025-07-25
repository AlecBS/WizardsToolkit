/*
The following functions/procedures have been added to the DB
and need to be in Staging DB to allow for anonymizing.

    generate_fname
    generate_lname
    generate_email

anonymize_data should never be added to Production DB
  because it would be CATASTROPHIC if it triggered
  and wiped out production data
*/

DROP PROCEDURE IF EXISTS anonymize_data;
DROP FUNCTION IF EXISTS generate_fname;
DROP FUNCTION IF EXISTS generate_lname;
DROP FUNCTION IF EXISTS generate_email;

DELIMITER $$

CREATE FUNCTION generate_fname()
    RETURNS varchar(255)
    DETERMINISTIC
BEGIN
RETURN ELT(FLOOR(1 + (RAND() * (100-1))),
    "James","Mary","John","Patricia","Robert","Linda","Michael","Barbara","William","Elizabeth","David","Jennifer","Richard","Maria","Charles","Susan","Joseph","Margaret","Thomas","Dorothy","Christopher","Lisa","Daniel","Nancy","Paul","Karen","Mark","Betty","Donald","Helen","George","Sandra","Kenneth","Donna","Steven","Carol","Edward","Ruth","Brian","Sharon","Ronald","Michelle","Anthony","Laura","Kevin","Sarah","Jason","Kimberly","Matthew","Deborah",
    "Gary","Jessica","Timothy","Shirley","Jose","Cynthia","Larry","Angela","Jeffrey","Melissa","Frank","Brenda","Scott","Amy","Eric","Anna","Stephen","Rebecca","Andrew","Virginia","Raymond","Kathleen","Gregory","Pamela","Joshua","Martha","Jerry","Debra","Dennis","Amanda","Walter","Stephanie","Patrick","Carolyn","Peter","Christine","Harold","Marie","Douglas","Janet","Henry","Catherine","Carl","Frances","Arthur","Ann","Ryan","Joyce","Roger","Diane");
END
$$

CREATE FUNCTION generate_lname()
    RETURNS varchar(255)
    DETERMINISTIC
BEGIN
RETURN ELT(FLOOR(1 + (RAND() * (100-1))), "Smith","Johnson","Williams","Jones","Brown","Davis","Miller","Wilson","Moore","Taylor","Anderson","Thomas","Jackson","White","Harris","Martin","Thompson","Garcia","Martinez","Robinson","Clark","Rodriguez","Lewis","Lee","Walker","Hall","Allen","Young","Hernandez","King","Wright","Lopez","Hill","Scott","Green","Adams","Baker","Gonzalez","Nelson","Carter","Mitchell","Perez","Roberts","Turner","Phillips","Campbell","Parker","Evans","Edwards",
    "Collins","Stewart","Sanchez","Morris","Rogers","Reed","Cook","Morgan","Bell","Murphy","Bailey","Rivera","Cooper","Richardson","Cox","Howard","Ward","Torres","Peterson","Gray","Ramirez","James","Watson","Brooks","Kelly","Sanders","Price","Bennett","Wood","Barnes","Ross","Henderson","Coleman","Jenkins","Perry","Powell","Long","Patterson","Hughes","Flores","Washington","Butler","Simmons","Foster","Gonzales","Bryant","Alexander","Russell","Griffin","Diaz","Hayes");
END
$$

-- Create email generation procedure
-- Generate a random email account for test data; great to use with generate_fname as first part of email
CREATE FUNCTION generate_email()
    RETURNS varchar(255)
    DETERMINISTIC
BEGIN
RETURN ELT(FLOOR(1 + (RAND() * (1000-1))), "@dagondesign.com",
    "@dmoz.org","@posterous.com","@washington.edu","@barnesandnoble.com","@alibaba.com","@pen.io","@sitemeter.com","@wired.com","@is.gd","@biblegateway.com","@geocities.jp","@domainmarket.com","@skype.com","@newsvine.com","@slideshare.net","@instagram.com",
    "@theguardian.com","@chicagotribune.com","@addthis.com","@weebly.com","@domainmarket.com","@microsoft.com","@techcrunch.com","@eepurl.com","@ning.com","@ifeng.com","@domainmarket.com","@wunderground.com","@sourceforge.net","@nba.com","@woothemes.com",
    "@amazonaws.com","@google.com","@apple.com","@noaa.gov","@infoseek.co.jp","@nytimes.com","@umich.edu","@de.vu","@phpbb.com","@slate.com","@flavors.me","@ed.gov","@ezinearticles.com","@theatlantic.com","@mediafire.com","@nhs.uk","@engadget.com","@cnbc.com",
    "@discovery.com","@google.cn","@jimdo.com","@howstuffworks.com","@cnn.com","@twitter.com","@vinaora.com","@ucoz.com","@csmonitor.com","@boston.com","@youtube.com","@dailymotion.com","@networkadvertising.org","@dropbox.com","@people.com.cn","@yellowbook.com",
    "@craigslist.org","@mapy.cz","@dmoz.org","@comsenz.com","@github.io","@soundcloud.com","@independent.co.uk","@mysql.com","@arizona.edu","@google.ca","@weather.com","@wikia.com","@paypal.com","@businesswire.com",
    "@hc360.com","@narod.ru","@tuttocitta.it","@nba.com","@deliciousdays.com","@qq.com","@php.net","@istockphoto.com","@friendfeed.com","@rambler.ru","@google.co.jp","@naver.com","@wsj.com","@rakuten.co.jp","@list-manage.com",
    "@cdc.gov","@aol.com","@cbc.ca","@earthlink.net","@digg.com","@gravatar.com","@forbes.com","@sun.com","@trellian.com","@prweb.com","@oracle.com","@japanpost.jp","@webs.com","@dion.ne.jp","@salon.com","@yahoo.co.jp","@icq.com",
    "@forbes.com","@cnbc.com","@diigo.com","@1und1.de","@123-reg.co.uk","@epa.gov","@examiner.com","@fema.gov","@multiply.com","@tripadvisor.com","@twitpic.com","@google.es","@upenn.edu","@yandex.ru","@ucoz.com","@unicef.org",
    "@networksolutions.com","@odnoklassniki.ru","@hubpages.com","@arizona.edu","@jalbum.net","@wikia.com","@goo.gl","@livejournal.com","@unc.edu",
    "@guardian.co.uk","@slate.com","@chicagotribune.com","@ovh.net","@yandex.ru","@goodreads.com","@ucoz.com","@timesonline.co.uk","@acquirethisname.com","@google.com.hk","@issuu.com","@php.net","@ucoz.ru","@mapquest.com","@purevolume.com","@trellian.com","@opensource.org",
    "@purevolume.com","@com.com","@nps.gov","@independent.co.uk","@livejournal.com","@google.ru","@wufoo.com","@japanpost.jp","@goo.ne.jp","@accuweather.com","@china.com.cn","@1688.com","@usnews.com","@blogs.com","@jigsy.com","@xinhuanet.com","@skype.com","@etsy.com",
    "@bloglovin.com","@techcrunch.com","@phpbb.com","@timesonline.co.uk","@lulu.com","@ycombinator.com","@wordpress.com","@digg.com","@plala.or.jp","@nydailynews.com","@slideshare.net","@columbia.edu","@odnoklassniki.ru","@ed.gov","@imgur.com",
    "@goodreads.com","@acquirethisname.com","@mayoclinic.com","@cloudflare.com","@webnode.com","@1688.com","@bandcamp.com","@angelfire.com","@bandcamp.com","@house.gov","@wordpress.org","@about.com","@1688.com","@cpanel.net","@usgs.gov","@icio.us","@nytimes.com",
    "@zdnet.com","@fotki.com","@ft.com","@dailymotion.com","@unesco.org","@spiegel.de","@icio.us","@wisc.edu","@fema.gov","@nps.gov","@about.com","@google.com.br","@reference.com","@statcounter.com","@bluehost.com","@disqus.com","@t.co","@ow.ly","@java.com",
    "@123-reg.co.uk","@who.int","@wikipedia.org","@businessinsider.com","@washingtonpost.com","@google.it","@craigslist.org","@quantcast.com","@virginia.edu","@sun.com","@hao123.com","@hexun.com","@google.nl","@webmd.com","@businessinsider.com","@gnu.org",
    "@sbwire.com","@gmpg.org","@weibo.com","@oakley.com","@woothemes.com","@so-net.ne.jp","@youtube.com","@buzzfeed.com","@imageshack.us","@spiegel.de","@godaddy.com","@joomla.org","@cnn.com","@sourceforge.net","@github.com","@reuters.com","@delicious.com","@foxnews.com","@geocities.com","@edublogs.org",
    "@themeforest.net","@nyu.edu","@woothemes.com","@youku.com","@hatena.ne.jp","@dropbox.com","@smugmug.com","@freewebs.com","@washington.edu","@tinypic.com","@moonfruit.com","@apple.com","@psu.edu","@vinaora.com","@wikipedia.org","@yahoo.com","@blogtalkradio.com","@lycos.com","@hibu.com",
    "@cargocollective.com","@google.com","@abc.net.au","@home.pl","@noaa.gov","@e-recht24.de","@upenn.edu","@prweb.com","@weibo.com","@ft.com","@abc.net.au","@mozilla.com","@businessweek.com","@miibeian.gov.cn","@addthis.com","@wordpress.com","@blogtalkradio.com","@gravatar.com","@reverbnation.com","@illinois.edu",
    "@youtu.be","@imdb.com","@mail.ru","@youku.com","@disqus.com","@java.com","@dedecms.com","@ucla.edu","@friendfeed.com","@independent.co.uk","@themeforest.net","@facebook.com","@mayoclinic.com","@reddit.com","@sciencedirect.com","@ifeng.com","@elpais.com","@cornell.edu","@acquirethisname.com","@buzzfeed.com",
    "@parallels.com","@liveinternet.ru","@who.int","@infoseek.co.jp","@indiatimes.com","@cmu.edu","@hp.com","@webnode.com","@over-blog.com","@bravesites.com","@hexun.com","@indiatimes.com","@ebay.com","@bloomberg.com","@geocities.com","@blogs.com","@arstechnica.com","@skype.com","@netscape.com","@trellian.com",
    "@house.gov","@nasa.gov","@sciencedirect.com","@list-manage.com","@springer.com","@storify.com","@imdb.com","@mac.com","@github.io","@prlog.org","@people.com.cn","@wunderground.com","@cbslocal.com","@walmart.com","@amazon.com","@mediafire.com","@wired.com","@moonfruit.com","@facebook.com","@devhub.com","@redcross.org",
    "@dot.gov","@prlog.org","@economist.com","@biblegateway.com","@blog.com","@admin.ch","@github.com","@blogspot.com","@digg.com","@ezinearticles.com","@yolasite.com","@nih.gov","@amazon.co.jp","@merriam-webster.com","@netscape.com","@eepurl.com","@unicef.org",
    "@dion.ne.jp","@hp.com","@apple.com","@telegraph.co.uk","@nifty.com","@epa.gov","@arizona.edu","@people.com.cn","@globo.com","@si.edu","@sfgate.com","@addthis.com","@pen.io","@creativecommons.org","@bigcartel.com","@va.gov","@wsj.com","@amazon.com","@google.it","@princeton.edu","@fda.gov","@japanpost.jp",
    "@multiply.com","@washingtonpost.com","@dyndns.org","@ucsd.edu","@t.co","@yellowbook.com","@ocn.ne.jp","@printfriendly.com","@google.co.jp","@princeton.edu","@altervista.org","@istockphoto.com","@freewebs.com","@parallels.com","@sogou.com","@reddit.com","@bbb.org","@examiner.com","@globo.com","@studiopress.com",
    "@deliciousdays.com","@mit.edu","@github.com","@telegraph.co.uk","@ucla.edu","@who.int","@theglobeandmail.com","@goodreads.com","@bbc.co.uk","@about.me","@newyorker.com","@sphinn.com","@vinaora.com","@google.co.jp","@sciencedirect.com","@unblog.fr","@surveymonkey.com","@seesaa.net","@soup.io","@nba.com",
    "@list-manage.com","@ovh.net","@g.co","@google.nl","@mashable.com","@accuweather.com","@mit.edu","@cbsnews.com","@wikipedia.org","@fda.gov","@blogtalkradio.com","@tinypic.com","@upenn.edu","@e-recht24.de","@bandcamp.com","@wix.com","@usa.gov","@fda.gov",
    "@princeton.edu","@blogtalkradio.com","@nifty.com","@google.de","@hexun.com","@prweb.com","@archive.org","@nytimes.com","@desdev.cn","@pbs.org","@cpanel.net","@cnet.com","@weather.com","@reddit.com","@stumbleupon.com","@netvibes.com","@who.int","@goo.ne.jp","@jugem.jp",
    "@edublogs.org","@jimdo.com","@g.co","@whitehouse.gov","@ucsd.edu","@seesaa.net","@time.com","@dell.com","@rediff.com","@wikispaces.com","@facebook.com","@netscape.com","@opera.com","@who.int","@fema.gov","@pcworld.com","@fda.gov","@wordpress.org","@google.it","@yale.edu","@umn.edu","@mlb.com","@umn.edu","@hp.com",
    "@tiny.cc","@google.com.au","@plala.or.jp","@zimbio.com","@patch.com","@squarespace.com","@diigo.com","@fema.gov","@t-online.de","@ucoz.ru","@senate.gov","@xrea.com","@goo.gl","@blogger.com","@timesonline.co.uk","@gov.uk","@netscape.com","@webnode.com","@over-blog.com","@skyrock.com","@reverbnation.com",
    "@nature.com","@issuu.com","@ehow.com","@vkontakte.ru","@vistaprint.com","@yahoo.co.jp","@alibaba.com","@typepad.com","@hao123.com","@apple.com","@fotki.com",
    "@nymag.com","@gizmodo.com","@godaddy.com","@princeton.edu","@diigo.com","@mysql.com","@about.com","@barnesandnoble.com","@lycos.com","@foxnews.com","@gizmodo.com","@livejournal.com","@intel.com","@slashdot.org","@dailymotion.com","@yellowbook.com","@princeton.edu","@taobao.com","@soup.io","@foxnews.com",
    "@uiuc.edu","@topsy.com","@github.io","@livejournal.com","@scientificamerican.com","@netlog.com","@people.com.cn","@skyrock.com","@google.com.au","@gravatar.com","@goo.gl","@ox.ac.uk","@uiuc.edu","@prlog.org","@hugedomains.com","@ucoz.ru","@sogou.com","@intel.com","@nba.com","@yahoo.com","@cbc.ca",
    "@parallels.com","@huffingtonpost.com","@yahoo.co.jp","@angelfire.com","@unblog.fr","@deliciousdays.com","@is.gd","@bbb.org","@amazon.co.jp","@state.tx.us","@vk.com","@jugem.jp","@japanpost.jp","@latimes.com","@51.la","@nhs.uk","@salon.com","@rediff.com","@auda.org.au","@joomla.org","@hexun.com",
    "@clickbank.net","@barnesandnoble.com","@nationalgeographic.com","@ask.com","@nymag.com","@fotki.com","@nymag.com","@eventbrite.com","@indiegogo.com","@elegantthemes.com","@blogspot.com","@amazonaws.com","@arizona.edu","@shop-pro.jp","@wired.com","@naver.com","@bravesites.com","@gov.uk","@dagondesign.com",
    "@boston.com","@nature.com","@oracle.com","@comcast.net","@microsoft.com","@acquirethisname.com","@nationalgeographic.com","@yellowbook.com","@desdev.cn","@state.gov","@psu.edu","@canalblog.com","@qq.com","@cisco.com","@pcworld.com","@sun.com","@state.gov","@blog.com","@umich.edu","@arstechnica.com",
    "@tamu.edu","@rambler.ru","@webmd.com","@icq.com","@last.fm","@fastcompany.com","@technorati.com","@nhs.uk","@cloudflare.com","@elpais.com","@spiegel.de","@quantcast.com","@sourceforge.net","@ftc.gov","@blogspot.com","@pcworld.com","@mit.edu","@newsvine.com","@geocities.com","@sbwire.com","@twitter.com",
    "@wikia.com","@home.pl","@usnews.com","@ustream.tv","@columbia.edu","@house.gov","@fema.gov","@netvibes.com","@ucla.edu","@ifeng.com","@xrea.com","@sfgate.com","@harvard.edu","@weebly.com","@pcworld.com","@zdnet.com","@microsoft.com","@vimeo.com","@linkedin.com","@wp.com","@huffingtonpost.com","@issuu.com",
    "@trellian.com","@apache.org","@webmd.com","@latimes.com","@bravesites.com","@nhs.uk","@usgs.gov","@a8.net","@godaddy.com","@ycombinator.com","@indiatimes.com","@miibeian.gov.cn","@joomla.org","@wix.com","@usatoday.com","@ehow.com","@nasa.gov","@bloglovin.com","@gov.uk","@nbcnews.com","@ycombinator.com",
    "@photobucket.com","@phpbb.com","@etsy.com","@histats.com","@msu.edu","@shop-pro.jp","@nba.com","@qq.com","@nasa.gov","@economist.com","@usnews.com","@bing.com","@wufoo.com","@geocities.com","@networkadvertising.org","@hao123.com","@ed.gov","@ihg.com","@dedecms.com","@prlog.org","@google.co.jp","@netlog.com",
    "@va.gov","@desdev.cn","@illinois.edu","@flavors.me","@devhub.com","@ibm.com","@sitemeter.com","@so-net.ne.jp","@yolasite.com","@skyrock.com","@youtube.com","@so-net.ne.jp","@army.mil","@salon.com","@wordpress.org","@uiuc.edu","@huffingtonpost.com","@rediff.com","@tinyurl.com",
    "@wikipedia.org","@wp.com","@amazon.de","@fema.gov","@istockphoto.com","@earthlink.net","@uol.com.br","@creativecommons.org","@pinterest.com","@unicef.org","@ameblo.jp","@stanford.edu","@geocities.jp","@ovh.net","@eepurl.com","@example.com","@oracle.com","@cnbc.com","@opera.com",
    "@chicagotribune.com","@indiegogo.com","@sina.com.cn","@liveinternet.ru","@economist.com","@mit.edu","@flavors.me","@sina.com.cn","@arstechnica.com","@wufoo.com","@nih.gov","@toplist.cz","@google.com.au","@t.co","@huffingtonpost.com","@gnu.org","@unesco.org","@hugedomains.com",
    "@twitpic.com","@dagondesign.com","@dion.ne.jp","@163.com","@msn.com","@tuttocitta.it","@smugmug.com","@bluehost.com","@cornell.edu","@about.me","@furl.net","@theatlantic.com","@biblegateway.com","@hhs.gov","@upenn.edu","@aol.com","@wufoo.com","@ft.com","@un.org","@springer.com","@house.gov",
    "@so-net.ne.jp","@wufoo.com","@ed.gov","@clickbank.net","@tinypic.com","@elegantthemes.com","@acquirethisname.com","@meetup.com","@indiatimes.com","@intel.com","@list-manage.com","@spotify.com","@comcast.net","@digg.com","@businessweek.com","@sogou.com","@cbc.ca","@unc.edu","@epa.gov",
    "@smh.com.au","@ycombinator.com","@instagram.com","@odnoklassniki.ru","@blinklist.com","@nih.gov","@wikia.com","@utexas.edu","@wikimedia.org","@paginegialle.it","@github.io","@dailymail.co.uk","@miitbeian.gov.cn","@godaddy.com","@moonfruit.com","@scribd.com","@dagondesign.com","@cbc.ca",
    "@craigslist.org","@goo.gl","@google.nl","@amazon.co.jp","@lycos.com","@hexun.com","@surveymonkey.com","@sun.com","@wisc.edu","@google.es","@flavors.me","@blinklist.com","@theatlantic.com","@phpbb.com","@barnesandnoble.com","@chronoengine.com","@1und1.de","@diigo.com","@lulu.com",
    "@rediff.com","@networksolutions.com","@shinystat.com","@imdb.com","@usgs.gov","@e-recht24.de","@yellowpages.com","@yahoo.com","@sohu.com","@intel.com","@etsy.com","@lulu.com","@cbslocal.com","@usa.gov","@g.co","@naver.com","@linkedin.com","@dion.ne.jp","@chronoengine.com","@noaa.gov",
    "@wordpress.com","@reuters.com","@state.gov","@bbb.org","@spotify.com","@list-manage.com","@huffingtonpost.com","@macromedia.com","@dell.com","@whitehouse.gov","@irs.gov","@comcast.net","@hp.com","@blogtalkradio.com","@jiathis.com","@google.co.jp","@yale.edu","@reference.com","@blog.com",
    "@cnn.com","@mayoclinic.com","@ifeng.com","@php.net","@gravatar.com","@furl.net","@uol.com.br","@dion.ne.jp","@un.org","@upenn.edu","@sakura.ne.jp","@latimes.com","@cocolog-nifty.com","@geocities.com","@patch.com","@weebly.com","@networkadvertising.org",
    "@google.co.jp","@livejournal.com","@netvibes.com","@moonfruit.com","@hubpages.com","@springer.com","@yale.edu","@prnewswire.com","@is.gd","@marketwatch.com","@microsoft.com","@mediafire.com","@nps.gov","@reverbnation.com","@godaddy.com","@umich.edu",
    "@umich.edu","@icq.com","@ocn.ne.jp","@sphinn.com","@aol.com","@xrea.com","@go.com","@businesswire.com","@springer.com","@trellian.com","@google.it","@cbsnews.com","@cbc.ca","@yelp.com","@g.co","@abc.net.au","@xing.com","@boston.com","@parallels.com",
    "@prlog.org","@yandex.ru","@symantec.com","@posterous.com","@usatoday.com","@slate.com","@amazon.com","@multiply.com","@wunderground.com","@godaddy.com","@foxnews.com","@sakura.ne.jp","@fc2.com","@4shared.com","@loc.gov","@domainmarket.com","@whitehouse.gov",
    "@google.pl","@thetimes.co.uk","@netscape.com","@live.com","@vinaora.com","@squidoo.com","@spotify.com","@house.gov","@topsy.com","@geocities.com","@ftc.gov","@booking.com","@domainmarket.com","@jiathis.com","@wsj.com","@shinystat.com","@people.com.cn",
    "@webnode.com","@addtoany.com","@hc360.com","@hugedomains.com","@amazonaws.com","@webmd.com","@prweb.com","@salon.com","@hhs.gov","@tinypic.com","@a8.net","@mayoclinic.com","@comcast.net","@nature.com","@msu.edu","@nyu.edu","@bluehost.com","@senate.gov",
    "@youtube.com","@nydailynews.com","@noaa.gov","@drupal.org","@usnews.com","@paginegialle.it");
END
$$

-- usage:  call anonymize_data();

CREATE PROCEDURE `anonymize_data`()
BEGIN
    TRUNCATE TABLE `wtkLoginLog`;
    TRUNCATE TABLE `wtkUpdateLog`;
    TRUNCATE TABLE `wtkUserHistory`;

    UPDATE `wtkUsers`
      SET `WebPassword` = IF (`SecurityLevel` > 49,`WebPassword`, NULL),
          `InternalNote` = NULL
    WHERE `UID` > 5; -- Change this based on whatever users you don't want affected

    -- BB Staff: FirstName does not change,
    --  sets PasswordReset to FirstName, email stays the same
    UPDATE `wtkUsers`
      SET `NewPassHash` = `FirstName`
    WHERE `SecurityLevel` > 49;

    UPDATE `wtkUsers`
      SET `FirstName` = generate_fname()
    WHERE `SecurityLevel` < 50;

    UPDATE `wtkUsers`
      SET `Email` = CONCAT(CAST(`FirstName` AS CHAR CHARACTER SET utf8mb4),FLOOR(RAND() * 369), CAST(generate_email() AS CHAR CHARACTER SET utf8mb4))
    WHERE `Email` IS NOT NULL AND `SecurityLevel` < 50;

    UPDATE `wtkUsers`
      SET `LastName` = generate_lname()
    WHERE `LastName` IS NOT NULL;

    UPDATE `wtkUsers`
      SET `Phone` = CONCAT('(', ROUND((RAND() * 900) + 100), ') 555-', ROUND((RAND() * 999) + 1000))
    WHERE `Phone` IS NOT NULL;

    UPDATE `wtkUsers`
      SET `CellPhone` = CONCAT('(', ROUND((RAND() * 900) + 100), ') 555-', ROUND((RAND() * 999) + 1000))
    WHERE `CellPhone` IS NOT NULL;

    UPDATE `wtkUsers`
      SET `AltEmail` = CONCAT(CAST(`FirstName` AS CHAR CHARACTER SET utf8mb4),FLOOR(RAND() * 369), CAST(generate_email() AS CHAR CHARACTER SET utf8mb4))
    WHERE `AltEmail` IS NOT NULL;

    UPDATE `wtkUsers`
      SET `Address` = '123 Some Street'
    WHERE `Address` IS NOT NULL;

    UPDATE `wtkUsers`
      SET `Address2` =
          CASE FLOOR(1 + (RAND() * 2))
              WHEN 1 THEN CONCAT('Apt. #', LPAD(FLOOR(RAND() * 1000), 3, '0'))
              WHEN 2 THEN CONCAT('Suite #', LPAD(FLOOR(RAND() * 1000), 3, '0'))
          END
    WHERE `Address2` IS NOT NULL;

    UPDATE `wtkRevenue`
        SET `FirstName` = 'some name', `LastName` = 'last name', `PayerEmail` = 'fake@email.com';

END;
$$

DELIMITER ;

CALL anonymize_data();  -- DANGER: only run on staging or development DBs
