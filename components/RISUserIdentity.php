<?php

namespace app\components;


class RISUserIdentity extends BaseUserIdentity
{
    /** @var BenutzerIn */
    private $benutzerIn;

    /**
     * @param BenutzerIn $benutzerIn
     */
    public function __construct($benutzerIn)
    {
        $this->benutzerIn = $benutzerIn;
    }


    /**
     * @return Bool
     */
    public function authenticate()
    {
        return false;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->benutzerIn->email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->benutzerIn->email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->benutzerIn->email;
    }


}