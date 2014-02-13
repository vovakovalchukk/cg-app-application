<?php
namespace Orders\Controller\Tag;

class Append
{
    public function __invoke(Request $request, array $tags)
    {
        $tags[] = $request->getTag();
        return $tags;
    }
}