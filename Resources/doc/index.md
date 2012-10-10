### Dokumentacja EE ExtraMailer'a


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

    $extraMailer->sendMessage(array('recipient@example.com' => 'Recipient Name'), 'EEExtraMailerBundle:Demo:sample.email.twig', array('foo' => 'bar');
        
```

Example view used by EEExtraMailer, all 3 blocks are mandatory.

``` twig

{% block subject %}Sample subject{% endblock %}

{% block body_text %}Sample content{% endblock %}

{% block body_html %}<p>Sample content</p>{% endblock %}


```

Attachments 


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
