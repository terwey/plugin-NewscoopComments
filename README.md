in `application/AppKernel.php` add the following:

```
public function registerBundles()
{
    $bundles = array(
        // ...
		new FOS\CommentBundle\FOSCommentBundle(),
		// …
	);
}
```

in `application/configs/symfony/config.yml` add:

```
fos_comment:
    db_driver: orm
    class:
        model:
            comment: Newscoop\CommentsBundle\Entity\Comment
            thread: Newscoop\CommentsBundle\Entity\Thread

assetic:
    bundles: [ "FOSCommentBundle" ]
```