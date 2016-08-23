<?php

namespace Unifik\MediaBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Unifik\MediaBundle\Entity\Folder;
use Unifik\MediaBundle\Entity\Media;
use Unifik\MediaBundle\Lib\MediaFile;

/**
 * A console command for installing unifik.
 *
 * This class is inspired from Sylius's install command.
 */
class UploadCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager $em 
     */
    
    protected $em;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('unifik:media:upload')
            ->setDescription('Upload files into the Media Library')
            ->setDefinition(array(
                    new InputArgument(
                        'target', InputArgument::REQUIRED,
                        'Directory or file to be uploaded.'
                    ),
                    new InputOption(
                        'folder', null, InputOption::VALUE_OPTIONAL,
                        'The name of the media folder in which the files should be sent'
                    ),
                    new InputOption(
                        'temporary-folder', null, InputOption::VALUE_OPTIONAL,
                        'The path to the temporary folder on the machine (files will be copied and then deleted from there)'
                    ),
                    new InputOption(
                        'no-temporary-folder', null, InputOption::VALUE_NONE,
                        'No temporary folder will be used (files will be directly moved from the target directory to their new location)'
                    ),
                    new InputOption(
                        'force', null, InputOption::VALUE_NONE,
                        'Force the upload ignoring the warning(s).'
                    )
                )
            )
            ->setHelp(<<<EOF
The <info>unifik:media:upload</info> command uploads a file into the Media Library
EOF
            )
        ;
    }

    /**
     * Main entry point
     *
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('upload_max_filesize', '50M');
        #ini_set('post_max_size', '50M');
        $targetArg = rtrim($input->getArgument('target'), '/');
        $folderOpt = $input->getOption('folder');
        $tmpOpt = $input->getOption('temporary-folder');
        $tmpOpt = $tmpOpt ? rtrim($tmpOpt, '/').'/' : '/tmp/';
        $noTmpOpt = true === $input->getOption('no-temporary-folder');
        $forceOpt = true === $input->getOption('force');

        if ($noTmpOpt) {
            $tmpOpt = '';
        }
        $isDir = is_dir($targetArg);

        if ($isDir) {
            $output->writeln('<info>Importing files from directory '.$targetArg.' ...</info>');
        } else if (file_exists($targetArg)) {
            $output->writeln('<info>Importing file '.$targetArg.' ...</info>');
        } else {
            $output->writeln('<error>'.$targetArg.' is not a valid target file/directory</error>');
            return 1;
        }
        $output->writeln('');

        if ($this->getContainer()->getParameter('unifik_doctrine_behaviors.uploadable.upload_root_dir') == '../web/uploads') {
            $output->writeln('<error>WARNING: The parameter "unifik_doctrine_behaviors.uploadable.upload_root_dir" is set to "../web/uploads". '.
                'In console, this might result in files not being sent at the right place.</error>');
            $output->writeln('');
            if (!$forceOpt) {
                $output->writeln('<error>Please adjust the setting or run the command again with --force to ignore this warning.</error>');
                return 1;
            }
        }

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $folder = false;
        if ($folderOpt) {
            $folder = $this->em->getRepository('UnifikMediaBundle:Folder')->createQueryBuilder('f')
                ->select('f')
                ->andWhere('f.name = :folder')
                ->setParameter('folder', $folderOpt)
                ->getQuery()->getOneOrNullResult();
            if (!$folder) {
                $output->writeln('<error>'.$folderOpt.' is not a valid media folder</error>');
                return 1;
            }
        }

        $i = 0;

        $files = [];
        if ($isDir) {
            $this->addDirectoryFiles($files, $targetArg);
        } else {
            $files[] = $targetArg;
        }

        $count = count($files);

        $output->writeln('<info>'.$count.' files will be uploaded!</info>');
        foreach ($files as $_file) {
            if (!$noTmpOpt) {
                if (!is_dir(dirname($tmpOpt . $_file))) {
                    mkdir(dirname($tmpOpt . $_file), 0777, true);
                }
                copy($_file, $tmpOpt . $_file);
            }
            $mimeType = mime_content_type($tmpOpt.$_file);
            $file = new UploadedFile(
                $tmpOpt.$_file,
                basename($tmpOpt.$_file),
                $mimeType,
                filesize($tmpOpt.$_file),
                false,
                true
            );

            switch ($file->getMimeType()) {
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                    $uploadFunction = 'imageUpload';
                    break;
                case 'video/mpeg':
                case 'video/mp4':
                case 'application/x-shockwave-flash':
                case 'video/x-flv':
                case 'video/quicktime':

                case 'video/x-ms-wmv':
                case 'video/x-msvideo':

                    $uploadFunction = 'videoUpload';
                    break;
                default:
                    $uploadFunction = 'documentUpload';
            }
            $this->$uploadFunction($file, $folder);

            $i++;
            $output->writeln('<info>'.$i.'</info> '.$_file.' was uploaded successfully (type: '.$mimeType.')');
        }

        $output->writeln('');
        $output->writeln('<info>'.$i > 1 ? $i.' files were uploaded.' : $i.' file was uploaded.</info>');
        $output->writeln('');

        return 0;
    }

    protected function addDirectoryFiles(&$files, $dirName) {
        $dirContent = array_diff(scandir($dirName), array('..', '.'));
        foreach ($dirContent as $file) {
            if (is_dir($dirName.'/'.$file)) {
                $this->addDirectoryFiles($files, $dirName.'/'.$file);
            } else {
                $files[] = $dirName.'/'.$file;
            }
        }
    }


    /**
     * imageUpload
     *
     * @param UploadedFile $file
     */
    protected function imageUpload(UploadedFile $file, $folder = false)
    {
        $media = new Media();

        $media->setType('image');
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());

        list($width, $height, $type, $attr) = getimagesize($file->getRealPath());

        $media->setWidth($width);
        $media->setHeight($height);
        $media->setMimeType($file->getClientMimeType());
        $media->setAttr($attr);
        $media->setSize($file->getClientSize());

        if ($folder) {
            $media->setFolder($folder);
        }

        $this->em->persist($media);

        $this->em->flush();
    }

    /**
     * videoUpload
     *
     * @param UploadedFile $file
     */
    protected function videoUpload(UploadedFile $file, $folder = false)
    {
        $media = new Media();
        $media->setType('video');
        $media->setContainer($this->getContainer());
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());
        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getClientSize());

        $this->em->persist($media);

        //Generate the thumbnail

        $thumbnailFile = new MediaFile($this->getVideoThumbnailPath($file));
        $thumbnailFile = $thumbnailFile->getUploadedFile();

        $image = new Media();

        $image->setType('image');
        $image->setHidden(true);
        $image->setName("Preview - ".$file->getClientOriginalName());
        $image->setMedia($thumbnailFile);

        list($width, $height, $type, $attr) = getimagesize($thumbnailFile->getRealPath());

        $image->setWidth($width);
        $image->setHeight($height);
        $image->setMimeType($thumbnailFile->getClientMimeType());
        $image->setAttr($attr);
        $image->setSize($thumbnailFile->getClientSize());

        if ($folder) {
            $media->setFolder($folder);
        }

        $this->em->persist($image);

        $media->setThumbnail($image);

        $this->em->flush();
    }

    /**
     * documentUpload
     *
     * @param UploadedFile $file
     */
    protected function documentUpload(UploadedFile $file, $folder = false)
    {
        $media = new Media();
        $media->setType('document');
        $media->setContainer($this->getContainer());
        $media->setMedia($file);
        $media->setName($file->getClientOriginalName());
        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getClientSize());

        if ($folder) {
            $media->setFolder($folder);
        }

        $this->em->persist($media);

        //Generate the thumbnail

        $thumbnailFile = new MediaFile($this->getThumbnailPath($file));
        $thumbnailFile = $thumbnailFile->getUploadedFile();

        $image = new Media();

        $image->setType('image');
        $image->setHidden(true);
        $image->setMedia($thumbnailFile);
        $image->setName("Preview - " . $file->getClientOriginalName());

        list($width, $height, $type, $attr) = getimagesize($thumbnailFile->getRealPath());

        $image->setWidth($width);
        $image->setHeight($height);
        $image->setMimeType($thumbnailFile->getClientMimeType());
        $image->setAttr($attr);
        $image->setSize($thumbnailFile->getClientSize());

        $this->em->persist($image);

        $media->setThumbnail($image);

        $this->em->flush([$image, $media]);
    }

    /**
     * Get the path of the thumbnail icon depending of the content
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getThumbnailPath(UploadedFile $file)
    {
        switch ($file->getMimeType()) {
            case 'application/pdf':
                return $this->createPdfPreview($file->getPathname());
            case 'application/msword':
                copy($this->getContainer()->get('kernel')->getRootDir().'/../web/bundles/unifikmedia/backend/images/icons/word-icon.png', '/tmp/word-icon.png');
                return '/tmp/word-icon.png';
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                copy($this->getContainer()->get('kernel')->getRootDir().'/../web/bundles/unifikmedia/backend/images/icons/word-icon.png', '/tmp/word-icon.png');
                return '/tmp/word-icon.png';
            case 'application/vnd.oasis.opendocument.text':
                copy($this->getContainer()->get('kernel')->getRootDir().'/../web/bundles/unifikmedia/backend/images/icons/writer-icon.jpg', '/tmp/writer-icon.jpg');
                return '/tmp/writer-icon.jpg';
            default:
                copy($this->getContainer()->get('kernel')->getRootDir().'/../web/bundles/unifikmedia/backend/images/icons/file-icon.png', '/tmp/file-icon.png');
                return '/tmp/file-icon.png';
        }
    }

    /**
     * Get the path of the thumbnail icon depending of the content
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function getVideoThumbnailPath(UploadedFile $file)
    {
        switch ($file->getMimeType()) {
            default:
                copy($this->getContainer()->get('kernel')->getRootDir().'/../web/bundles/unifikmedia/backend/images/icons/video-icon.png', '/tmp/video-icon.png');
                return '/tmp/video-icon.png';
        }
    }

    /**
     * Generate a pdf preview if "convert" is present on the host system
     *
     * @param $path
     * @return string
     */
    protected function createPdfPreview($path)
    {
        if (shell_exec("which convert")) {
            $target = $path.'.jpg';
            $command = sprintf("convert %s[0] %s", $path, $target);
            if (!shell_exec($command)) {
                return $target;
            }
        }

        copy($this->getContainer()->get('kernel')->getRootDir().'/../web/bundles/unifikmedia/backend/images/icons/pdf-icon.png', '/tmp/pdf-icon.png');
        return '/tmp/pdf-icon.png';
    }
}

