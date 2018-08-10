###General:
- Email handler Bundle
- Relies on SwiftMailer
- The ***SimpleMailerService*** is autowired and thus available in containerAware context


### Usage:
- The bundle provides a ***SimpleMailerService*** which will be used to handle email rendering and sending
```php
    $mailerService = new SimpleMailerService($mailer, $twigEngine, $emailRepository);
    
    /* @var Email $email */
    foreach ($emails as $email) {
        $mailerService->send($email, false); // if the $doSave is set to true, the email object will be saved in the DB 
    }
```
