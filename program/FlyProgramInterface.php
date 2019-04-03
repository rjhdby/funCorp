<?php

namespace program;

interface FlyProgramInterface
{
    public function __construct(int $startUp, array $operations);

    public function getDelta(): int;

    public function getStartUp(): int;

    public function getNext(): ?array;

    public function isFinished(): bool;
}