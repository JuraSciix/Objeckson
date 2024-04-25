<?php

namespace jurasciix\objeckson\test;

use jurasciix\objeckson\JsonProperty;
use jurasciix\objeckson\Optional;

class IssueModel {

    #[JsonProperty]
    public string $title;

    // Optional....
    #[JsonProperty]
    #[Optional]
    public string $comment = "";

    #[JsonProperty]
    public IssueStatusModel $status;
}