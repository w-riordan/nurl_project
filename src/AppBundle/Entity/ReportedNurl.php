<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReportedNurl
 *
 * @ORM\Table(name="reported_nurl")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReportedNurlRepository")
 */
class ReportedNurl
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="accepted", type="boolean", nullable=true)
     */
    private $accepted;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text")
     */
    private $reason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=true)
     */
    private $timestamp;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="Nurl")
     * @ORM\JoinColumn(name="nurl", referencedColumnName="id")
     */
    private $nurl;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     *
     * @return ReportedNurl
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return bool
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set reason
     *
     * @param string $reason
     *
     * @return ReportedNurl
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     *
     * @return ReportedNurl
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set nurl
     *
     * @param \AppBundle\Entity\Nurl $nurl
     *
     * @return ReportedNurl
     */
    public function setNurl(\AppBundle\Entity\Nurl $nurl = null)
    {
        $this->nurl = $nurl;

        return $this;
    }

    /**
     * Get nurl
     *
     * @return \AppBundle\Entity\Nurl
     */
    public function getNurl()
    {
        return $this->nurl;
    }
}
