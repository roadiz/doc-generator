<?php
declare(strict_types=1);

namespace RZ\Roadiz\Documentation\Generators;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @package RZ\Roadiz\Documentation\Generators
 */
class NodeTypeGenerator
{
    protected TranslatorInterface $translator;
    protected MarkdownGeneratorFactory $markdownGeneratorFactory;
    private NodeTypeInterface $nodeType;
    private array $fieldGenerators;
    private ParameterBag $nodeTypesBag;

    /**
     * @param NodeTypeInterface $nodeType
     * @param ParameterBag $nodeTypesBag
     * @param TranslatorInterface $translator
     * @param MarkdownGeneratorFactory $markdownGeneratorFactory
     */
    public function __construct(
        NodeTypeInterface $nodeType,
        ParameterBag $nodeTypesBag,
        TranslatorInterface $translator,
        MarkdownGeneratorFactory $markdownGeneratorFactory
    ) {
        $this->nodeType = $nodeType;
        $this->nodeTypesBag = $nodeTypesBag;
        $this->fieldGenerators = [];
        $this->translator = $translator;
        $this->markdownGeneratorFactory = $markdownGeneratorFactory;

        /** @var NodeTypeFieldInterface $field */
        foreach ($this->nodeType->getFields() as $field) {
            $this->fieldGenerators[] = $this->markdownGeneratorFactory->createForNodeTypeField($field);
        }
    }

    public function getMenuEntry(): string
    {
        return '['.$this->nodeType->getLabel().']('.$this->getPath().')';
    }

    public function getType(): string
    {
        return $this->nodeType->isReachable() ? 'page' : 'block';
    }

    public function getPath(): string
    {
        return $this->getType() . '/' . $this->nodeType->getName() . '.md';
    }

    public function getContents(): string
    {
        return implode("\n\n", [
            $this->getIntroduction(),
            '## ' . $this->translator->trans('docs.fields'),
            $this->getFieldsContents()
        ]);
    }

    protected function getIntroduction(): string
    {
        $lines = [
            '# ' . $this->nodeType->getLabel(),
        ];
        if (!empty($this->nodeType->getDescription())) {
            $lines[] = $this->nodeType->getDescription();
        }
        $lines = array_merge($lines, [
            '',
            '|     |     |',
            '| --- | --- |',
            '| **' . trim($this->translator->trans('docs.technical_name')) . '** | `' . $this->nodeType->getName() . '` |',
        ]);

        if ($this->nodeType->isPublishable()) {
            $lines[] = '| **' . trim($this->translator->trans('docs.publishable')) . '** | *' . $this->markdownGeneratorFactory->getHumanBool($this->nodeType->isPublishable()) . '* |';
        }
        if (!$this->nodeType->isVisible()) {
            $lines[] = '| **' . trim($this->translator->trans('docs.visible')). '** | *' . $this->markdownGeneratorFactory->getHumanBool($this->nodeType->isVisible()) . '* |';
        }

        return implode("\n", $lines);
    }

    protected function getFieldsContents(): string
    {
        return implode("\n", array_map(function (AbstractFieldGenerator $abstractFieldGenerator) {
            return $abstractFieldGenerator->getContents();
        }, $this->fieldGenerators));
    }
}
