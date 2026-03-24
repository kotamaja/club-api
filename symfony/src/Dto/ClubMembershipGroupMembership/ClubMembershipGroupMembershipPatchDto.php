<?php

namespace App\Dto\ClubMembershipGroupMembership;

class ClubMembershipGroupMembershipPatchDto
{
    private ?string $notes;


    private bool $notesProvided = false;

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notesProvided = true;
        $this->notes = $notes;
    }

    public function isNotesProvided(): bool
    {
        return $this->notesProvided;
    }



}
