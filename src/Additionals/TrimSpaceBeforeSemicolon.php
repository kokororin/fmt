<?php
# Copyright (c) 2014, phpfmt and its authors
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
#
# 3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

final class TrimSpaceBeforeSemicolon extends AdditionalPass {
	/**
	 * @param $source
	 * @param $foundTokens
	 */
	public function candidate($source, $foundTokens) {
		return true;
	}

	/**
	 * @param  $source
	 * @return mixed
	 */
	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';
		$isComment = false;
		while (list($index, $token) = eachArray($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
				case ST_SEMI_COLON:
					if (!$this->leftTokenIs([T_COMMENT, T_DOC_COMMENT])) {
						$this->rtrimAndAppendCode($text);
						break;
					}

				default:
					$this->appendCode($text);
					break;
			}
		}

		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Remove empty lines before semi-colon.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return <<<'EOT'
<?php
// From
echo 1
;

// To
echo 1;
?>
EOT;
	}
}
