<?php

namespace Wrapper;

interface IWrapper
{
    public function init(): bool;
    public function openDevice();
}
