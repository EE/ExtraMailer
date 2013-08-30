### Installation

Download EEExtraMailerBundle using composer

Add EEExtraMailerBundle in your composer.json:

```
{
    "require": {
        "ee/extramailer-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

```
$ php composer.phar update ee/extramailer-bundle
```

Composer will install the bundle to your project's vendor/ee directory.

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new EE\ExtraMailerBundle\EEExtraMailerBundle(),
    );
}

```

Configuration
-------------

``` yaml
# app/config/config.yml

ee_extra_mailer:
    from_email:
        address: no-reply@example.com
        sender_name: Example.com do not reply
```


Example usage within controller

``` php

    $extraMailer = $this->get('extramailer');
        
    $extraMailer->sendMessage(array('recipient@example.com' => 'Recipient Name'), 'EEExtraMailerBundle:Demo:sample.email.twig');

```

Example usage within controller with context

``` php

    $extraMailer->sendMessage(array('recipient@example.com' => 'Recipient Name'), 'EEExtraMailerBundle:Demo:sample.email.twig', array('foo' => 'bar'));
        
```

Example view used by EEExtraMailer, all 3 blocks are mandatory.

``` twig

{% block subject %}Sample subject{% endblock %}

{% block body_text %}Sample content{% endblock %}

{% block body_html %}<p>Sample content</p>{% endblock %}


```
## Themes

ExtraMailer supports themes, the list of available themes is [here](../views/Themes)

To change it

``` yaml
ee_extra_mailer:
    theme: FloralWhite # default one
```

Feel free to send a Pull Request if you want to add your theme to the list.

## Attachments


``` php
    
$extraMailer = $this->get('extramailer');
             
$options = array();

if($sampleEntity->getFileWebPath() !== null){
     $options['attachments'][] = $sampleEntity->getFileWebPath();  
}

$extraMailer->sendMessage(
        array('recipient@example.com' => 'Recipient Name'),
        'EEExtraMailerBundle:Demo:sample.email.twig',
        array(),
        $options
    );

unset($options);

```
