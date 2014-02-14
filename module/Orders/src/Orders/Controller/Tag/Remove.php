<?php
namespace Orders\Controller\Tag;

class Remove
{
    public function __invoke(Request $request, array $tags)
    {
        $tags = array_combine($tags, $tags);
        unset($tags[$request->getTag()]);
        return $tags;
    }
}