<?php

namespace Pushword\Conversation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pushword\Conversation\Repository\MessageRepository;
use Pushword\Core\Entity\SharedTrait\HostTrait;
use Pushword\Core\Entity\SharedTrait\IdTrait;
use Pushword\Core\Entity\SharedTrait\TimestampableTrait;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message implements \Stringable
{
    use HostTrait;
    use IdTrait;
    use TimestampableTrait;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 180, nullable: true)]
    protected ?string $authorName = '';

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 180, nullable: true)]
    protected ?string $authorEmail = '';

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER, nullable: true)]
    protected ?int $authorIp = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 200000, minMessage: 'conversation.content.short', maxMessage: 'conversation.content.long')]
    protected ?string $content = null;

    /**
     * Identifier referring (most of time, URI).
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 180)]
    protected string $referring = '';

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $publishedAt = null;

    public function __construct()
    {
        $this->updatedAt ??= new \DateTime();
        $this->createdAt ??= new \DateTime();
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Set message content.
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get message content.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Get the value of authorName.
     */
    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    /**
     * Set the value of authorName.
     */
    public function setAuthorName(?string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * Get the value of authorEmail.
     */
    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    /**
     * Set the value of authorEmail.
     */
    public function setAuthorEmail(?string $authorEmail): self
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    /**
     * Get identifier referring.
     */
    public function getReferring(): string
    {
        return $this->referring;
    }

    /**
     * Set identifier referring.
     */
    public function setReferring(string $referring): self
    {
        $this->referring = $referring;

        return $this;
    }

    /**
     * Get the value of authorIp.
     */
    public function getAuthorIp(): ?int
    {
        return $this->authorIp;
    }

    /**
     * Set the value of authorIp.
     */
    public function setAuthorIp(?int $authorIp): self
    {
        if (null !== $authorIp) {
            $this->authorIp = $authorIp;
        }

        return $this;
    }

    public function setAuthorIpRaw(string $authorIp): self
    {
        return $this->setAuthorIp((int) ip2long(IPUtils::anonymize($authorIp)));
    }

    public function getAuthorIpRaw(): bool|string
    {
        return long2ip((int) $this->getAuthorIp());
    }

    public function __toString(): string
    {
        return $this->id.' ';
    }
}
