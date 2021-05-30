<?php

namespace Pushword\Conversation\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ConversationAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues = [
            '_page' => 1,
            '_sort_order' => 'DESC',
            '_sort_by' => 'createdAt',
            '_per_page' => 100,
        ];
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->with('admin.conversation.label.conversation', ['class' => 'col-md-8'])
            ->add('content', TextareaType::class, [
                'attr' => ['rows' => 6],
                'label' => 'admin.conversation.content.label',
            ])
            ->add('referring', TextType::class, [
                'label' => 'admin.conversation.referring.label',
            ])
            ->add('createdAt', DateTimePickerType::class, [
                'label' => 'admin.conversation.createdAt.label',
            ])
            ->end();

        $form->with('admin.conversation.label.author', ['class' => 'col-md-4'])
            ->add('authorEmail', null, [
                'label' => 'admin.conversation.authorEmail.label',
            ])
            ->add('authorName', null, [
                'required' => false,
                'label' => 'admin.conversation.authorName.label',
            ])
            ->add('authorIp', null, [
                'required' => false,
                'label' => 'admin.conversation.authorIp.label',
                'attr' => [
                    ($this->getSubject() ? ($this->getSubject()->getAuthorIp() ? 'disabled' : 't') : 't') => '',
                ],
            ])
            ->end();

        $form->with('admin.conversation.label.publishedAt', ['class' => 'col-md-4'])
            ->add('publishedAt', DateTimePickerType::class, [
                'required' => false,
                'label' => '',
            ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('referring', null, [
            'label' => 'admin.conversation.from.label',
        ]);
        $filter->add('authorEmail', null, [
            'label' => 'admin.conversation.authorEmail.label',
        ]);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('referring', TextType::class)
            ->addIdentifier('content')
            ->add('authorEmail')
            ->add('authorName')
            ->add('authorIpRaw')
            ->add('createdAt')
            ->add('publishedAt');
    }
}
