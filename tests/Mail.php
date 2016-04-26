<?php
/**
 * Mail.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

class MailTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $config = new \FMUP\Config;
        $config2 = clone $config;
        /**
         * @var $config \FMUP\Config\ConfigInterface
         * @var $config2 \FMUP\Config\ConfigInterface
         */
        $config->set('smtp_secure', 'tls');
        $config->set('smtp_serveur', 'smtp_serveur');
        $config->set('smtp_port', 80);
        $config->set('smtp_authentification', true);
        $config->set('smtp_username', 'smtp_username');
        $config->set('smtp_password', 'smtp_password');
        $config2->set('smtp_serveur', 'localhost');

        $mail = new \FMUP\Mail($config);
        $mail2 = new \FMUP\Mail($config2);
        $this->assertEquals('smtp', $mail->Mailer);
        $this->assertEquals('mail', $mail2->Mailer);
        $this->assertTrue($mail->SMTPAuth);
        $this->assertNull($mail2->SMTPAuth);
        $this->assertEquals('tls', $mail->SMTPSecure);
        $this->assertNull($mail2->SMTPSecure);
        $this->assertTrue($mail->SMTPAutoTLS);
        $this->assertFalse($mail2->SMTPAutoTLS);
        $this->assertEquals('smtp_serveur', $mail->Host);
        $this->assertEquals('localhost', $mail2->Host);
        $this->assertEquals(80, $mail->Port);
        $this->assertNull($mail2->Port);
        $this->assertEquals('smtp_username', $mail->Username);
        $this->assertEquals('smtp_password', $mail->Password);
        $this->assertEquals('', $mail2->Username);
        $this->assertEquals('', $mail2->Password);
    }

    public function testReplaceTokens()
    {
        $message = 'This is a {adv} message from {name}';
        $tokens = array(
            array(
                'adv' => 'great',
                'name' => 'me',
            ),
            array(
                'adv' => 'brilliant',
                'name' => 'Larry Page',
            )
        );

        $this->assertSame('This is a great message from me', \FMUP\Mail::replaceTokens($message, $tokens[0]));
        $this->assertSame('This is a brilliant message from Larry Page', \FMUP\Mail::replaceTokens($message, $tokens[1]));
    }
}
