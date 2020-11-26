<?php

namespace CoasterCommerce\Core\Mailables;

class CustomerImportMailable extends AbstractMailable
{

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * Create a new message instance.
     *
     * @param string $password
     * @param string $email
     * @return void
     */
    public function __construct($password, $email)
    {
        $this->password = $password;
        $this->email = $email;
    }

    /**
     * @return static
     */
    public function build()
    {
        return $this
            ->to($this->email)
            ->markdown('coaster-commerce::emails.templates.customer-import', [
                'password' => $this->password,
                'email' => $this->email
            ]);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        return ['password', 'test@exmaple.com'];
    }

}
