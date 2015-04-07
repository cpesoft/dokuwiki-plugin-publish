<?php
/**
 * Unittests for the mail functionality of the publish plugin
 *
 * @group plugin_publish
 * @group plugin_publish_unittests
 * @group plugins
 * @group unittests
 * @author Michael Große <grosse@cosmocode.de>
 */
class publish_mail_unit_test extends DokuWikiTest {

    protected $pluginsEnabled = array('publish');

    /**
     * @covers action_plugin_publish_mail::difflink
     */
    function test_difflink () {
        global $ID;
        $ID = 'wiki:syntax';

        /** @var helper_plugin_publish $helper*/
        $helper = plugin_load('helper','publish');
        $actual_difflink = $helper->getDifflink('wiki:syntax','1','2');
        $expected_difflink = 'http://wiki.example.com/./doku.php?id=wiki:syntax&do=diff&rev2[0]=1&rev2[1]=2&difftype=sidebyside';
        $this->assertSame($expected_difflink,$actual_difflink);
    }

    /**
     * @covers action_plugin_publish_mail::apprejlink
     */
    function test_apprejlink () {
        global $ID;
        $ID = 'wiki:syntax';
        $mail = new action_plugin_publish_mail;
        $actual_apprejlink = $mail->apprejlink('wiki:syntax','1');
        $expected_apprejlink = 'http://wiki.example.com/./doku.php?id=wiki:syntax&rev=1'; //this stray dot comes from an unclean test-setup
        $this->assertSame($expected_apprejlink, $actual_apprejlink);
    }

    /**
     * @covers action_plugin_publish_mail::create_mail_body
     * @group slow
     */
    function test_change_mail_body () {
        global $ID;
        $ID = 'start';
        global $USERINFO;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['name'] = 'John Smith';
        saveWikiText('start', 'start first', 'foobar');
        $oldrevision = pageinfo();
        $oldrevision = $oldrevision['lastmod'];
        sleep(1);
        saveWikiText('start', 'start second', 'foobar');
        $newrevision = pageinfo();
        $newrevision = $newrevision['lastmod'];

        $expected_mail_body = 'Hi John Smith!
A new suggestion for My Test Wiki at http://wiki.example.com/./

View and approve: http://wiki.example.com/./doku.php?id=start&rev=' . $newrevision . '

Changes from previous version: http://wiki.example.com/./doku.php?id=start&do=diff&rev2[0]=' . $oldrevision . '&rev2[1]=' . $newrevision . '&difftype=sidebyside

--
This mail was generated by DokuWiki at
http://wiki.example.com/./';


        $mail = new action_plugin_publish_mail;
        $data = pageinfo();
        $actual_mail_body = $mail->create_mail_body('change');

        $this->assertSame($expected_mail_body, $actual_mail_body);

    }


    /**
     * @covers action_plugin_publish_mail::create_mail_body
     */
    function test_approve_mail_body () {
        global $ID;
        $ID = 'start';
        global $USERINFO;
        $_SERVER['REMOTE_USER'] = 'john';
        $USERINFO['name'] = 'John Smith';
        saveWikiText('start', 'start first', 'foobar');
        $revision = pageinfo();
        $revision = $revision['lastmod'];

        $expected_mail_body = 'Hi John Smith!
Your suggestion for My Test Wiki at http://wiki.example.com/./

URL: http://wiki.example.com/./doku.php?id=start&rev=' . $revision . '

is approved.

--
This mail was generated by DokuWiki at
http://wiki.example.com/./';


        $mail = new action_plugin_publish_mail;
        $data = pageinfo();
        $actual_mail_body = $mail->create_mail_body('approve');

        $this->assertSame($expected_mail_body, $actual_mail_body);

    }
}
