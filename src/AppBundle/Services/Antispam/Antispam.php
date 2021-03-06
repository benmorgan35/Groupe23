<?php

namespace AppBundle\Services\Antispam;



Class Antispam
{
    /**
     * Vérifie si le texte est un spam ou non
     *
     * @param string $text
     * @return array
     */
    public function isSpam($text)
    {
        $spam = false;
        $message = '';



        // Longueur du commentaire
        if (strlen($text) < 5)
        {
            $spam = true;
            $message = "Votre commentaire doit comporter 5 caractères minimum";
        }

        // Présence d'URL
        if (preg_match('/\b(https?:\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$])/i', $text)){

            $spam = true;
            $message = "Votre commentaire ne doit pas contenir de lien URL";
        }

        // Réécriture des adresse mails
        if (preg_match('/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})/i', $text, $regs)) {

            foreach ($regs as $reg){
                $regReplace = str_replace('@', ' _AT_ ', $reg);
                $text = str_replace($reg, $regReplace, $text);

            }
        }




        return array(
            'spam' => $spam,
            'message' => $message,
            'content' => $text
        );
    }
}
