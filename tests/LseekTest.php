<?php

class LseekTest extends PHPUnit_Framework_TestCase
{
	// The URI of the test file seen through Samba:
	private $testuri = 'smb://localhost/testshare/lseektest.txt';

	// The "real" file on the filesystem:
	private $realfile = '/home/testuser/testshare/lseektest.txt';

	public function
	testLseekSet ()
	{
		$state = smbclient_state_new();
		smbclient_state_init($state, null, 'testuser', 'password');
		$file = smbclient_creat($state, $this->testuri);
		$ret = smbclient_write($state, $file, 'abcdefgh');
		$this->assertTrue(is_integer($ret));

		$ret = smbclient_lseek($state, $file, 3, SEEK_SET);
		$this->assertEquals(3, $ret);

		smbclient_write($state, $file, 'foo');
		smbclient_close($state, $file);
		$this->assertStringEqualsFile($this->realfile, 'abcfoogh');
	}

	public function
	testLseekSetPastEnd ()
	{
		$state = smbclient_state_new();
		smbclient_state_init($state, null, 'testuser', 'password');
		$file = smbclient_creat($state, $this->testuri);

		$ret = smbclient_lseek($state, $file, 3, SEEK_SET);
		$this->assertEquals(3, $ret);

		smbclient_write($state, $file, 'foo');
		smbclient_close($state, $file);
		$this->assertStringEqualsFile($this->realfile, sprintf('%c%c%c%s', 0, 0, 0, 'foo'));
	}

	public function
	testLseekCurPositive ()
	{
		$state = smbclient_state_new();
		smbclient_state_init($state, null, 'testuser', 'password');
		$file = smbclient_creat($state, $this->testuri);
		smbclient_write($state, $file, 'abcdefgh');

		$ret = smbclient_lseek($state, $file, 3, SEEK_CUR);
		$this->assertEquals(11, $ret);

		smbclient_write($state, $file, 'foo', 3);
		smbclient_close($state, $file);
		$this->assertStringEqualsFile($this->realfile, sprintf('%s%c%c%c%s', 'abcdefgh', 0, 0, 0, 'foo'));
	}
}
