<?php

namespace App\Tests\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for the controllers defined inside the UserController used
 * for managing the users in the backend.
 *
 * See https://symfony.com/doc/current/testing.html#functional-tests
 *
 * Whenever you test resources protected by a firewall, consider using the
 * technique explained in:
 * https://symfony.com/doc/current/testing/http_authentication.html
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 */
class UserControllerTest extends WebTestCase
{
    
    protected $testAdminCreds = [
        'PHP_AUTH_USER' => 'jane_admin',
        'PHP_AUTH_PW' => 'kitten',
    ];    
    
    protected $testRegularUserCreds = [
        'PHP_AUTH_USER' => 'john_user',
        'PHP_AUTH_PW' => 'kitten',
    ];    
    
    /**
     * @dataProvider getUrlsForUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url): void
    {
        $client = static::createClient([], $this->testRegularUserCreds);

        $client->request($httpMethod, $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    
    /**
     * @dataProvider getUrlsForUsers
     */    
    public function testAccessGrantedForAdminUsers(string $httpMethod, string $url): void
    {
        $client = static::createClient([], $this->testAdminCreds);

        $client->request($httpMethod, $url);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function getUrlsForUsers(): ?\Generator
    {
        yield ['GET', '/en/admin/user/'];
        yield ['GET', '/en/admin/user/new'];
        yield ['GET', '/en/admin/user/1'];
        yield ['GET', '/en/admin/user/1/edit'];
    }

    public function testAdminNewUser(): void
    {
        $name = $this->getTestUserName();
        $email = $this->getTestUserEmail();
        $notes = $this->getTestUserNotes();
        
        $client = static::createClient([], $this->testAdminCreds);
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => $name,
            'user[email]' => $email,
            'user[notes]' => $notes,
        ]);

        $this->assertResponseRedirects('/en/admin/user/', Response::HTTP_FOUND);

        /** @var \App\Entity\User $user */
        $user = self::$container->get(UserRepository::class)->findOneByName($name);
        $this->assertNotNull($user);
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($notes, $user->getNotes());
    }

    public function testAdminShowUser(): void
    {
        $client = static::createClient([], $this->testAdminCreds);
        $client->request('GET', '/en/admin/user/1');
    
        $this->assertResponseIsSuccessful();
    }

    public function testAdminUserEmptyName(): void
    {                      
        $client = static::createClient([], $this->testAdminCreds);
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => '',
            'user[email]' => $this->getTestUserEmail(),
        ]);
                
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Name');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 'This value should not be blank.');
    }
    
    public function testAdminUserEmptyEmail(): void
    {        
        $client = static::createClient([], $this->testAdminCreds);
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => $this->getTestUserName(),
            'user[email]' => '',
        ]);
                
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Email');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 'This value should not be blank.');
    }    

    public function testAdminUserUniqueName(): void
    {        
        $client = static::createClient([], $this->testAdminCreds);
        $crawler = $client->request('GET', '/en/admin/user/new');
        $form = $crawler->selectButton('Create user')->form([
            'user[name]' => $this->getTestUserName(),
            'user[email]' => $this->getTestUserEmail(),
        ]);
        
        $client->submit($form); //creating user                 
        $client->submit($form); // trying to create same user one more time
        
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Name');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 'This value is already used.');
    }

    public function testAdminUserUniqueEmail(): void
    {        
        $email = $this->getTestUserEmail();

        $client = static::createClient([], $this->testAdminCreds);
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => $this->getTestUserName(),
            'user[email]' => $email,
        ]);
        
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => $this->getTestUserName(),
            'user[email]' => $email,
            'user[notes]' => '',
        ]);
        
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Email');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 'This value is already used.');
    }

    public function testAdminUserIncorrectName(): void
    {
        $client = static::createClient([], $this->testAdminCreds);
                
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => ucfirst($this->getTestUserName()),
            'user[email]' => $this->getTestUserEmail(),
        ]);
                
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Name');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 
            'The user name must contain only lowercase latin characters and digits.');
    }

    public function testAdminUserToshortName(): void
    {
        $client = static::createClient([], $this->testAdminCreds);
                
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => 'name'.random_int(1, 9),
            'user[email]' => $this->getTestUserEmail(),
        ]);
                
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Name');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 
            'User name must be at least 8 characters long.');
    }

    public function testAdminUserBlacklistedName(): void
    {
        $client = static::createClient([], $this->testAdminCreds);
        
        $blacklistedNames = $client->getKernel()->getContainer()->getParameter('app.blacklist.names');
        
        $name = $this->getTestUserName().$blacklistedNames[0];
        
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => $name,
            'user[email]' => $this->getTestUserEmail(),
        ]);
                
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Name');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 
            'The name "'.$name.'" contains word from blacklist');
    }

    public function testAdminUserBlacklistedEmail(): void
    {                      
        $client = static::createClient([], $this->testAdminCreds);
        
        $blacklistedEmailDomains = 
            $client->getKernel()->getContainer()->getParameter('app.blacklist.email_domains');
        
        $email = 'test_email'.random_int(10,99).'@'.$blacklistedEmailDomains[0];
        $client->request('GET', '/en/admin/user/new');
        $client->submitForm('Create user', [
            'user[name]' => $this->getTestUserName(),
            'user[email]' => $email,
        ]);
                
        $this->assertSelectorTextSame('form .form-group.has-error label', 'Email');
        $this->assertSelectorTextContains('form .form-group.has-error .help-block', 
            'The domain for email "'.$email.'" is in blacklist');
    }

    public function testAdminEditUser(): void
    {
        $newName = $this->getTestUserName();    
        $client = static::createClient([], $this->testAdminCreds);
        $client->request('GET', '/en/admin/user/1/edit');
        $client->submitForm('Save', [
            'user[name]' => $newName,
        ]);
    
        $this->assertResponseRedirects('/en/admin/user/', Response::HTTP_FOUND);
    
        /** @var \App\Entity\User $user */
        $user = self::$container->get(UserRepository::class)->find(1);
        $this->assertSame($newName, $user->getName());
    }

    public function testAdminDeleteUser(): void
    {
        $client = static::createClient([], $this->testAdminCreds);
        $crawler = $client->request('GET', '/en/admin/user/1');
        $client->submit($crawler->filter('#delete-form')->form());
    
        $this->assertResponseRedirects('/en/admin/user/', Response::HTTP_FOUND);
    
        /** @var \App\Entity\User $user */
        $user = self::$container->get(UserRepository::class)->find(1);
        $this->assertNotNull($user->getDeleted());
    }


    private function getTestUserName(int $min = 10, int $max = 99): string
    {
        return 'username'.random_int($min, $max);
    } 
    
    private function getTestUserEmail(int $min = 10, int $max = 99): string
    {
        return 'email@test'.random_int($min, $max).'.com';
    }     
    
    private function getTestUserNotes(int $length = 255): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return mb_substr(str_shuffle(str_repeat($chars, ceil($length / mb_strlen($chars)))), 1, $length);
    }
}
