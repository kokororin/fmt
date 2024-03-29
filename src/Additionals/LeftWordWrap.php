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

final class LeftWordWrap extends AdditionalPass {
	const PLACEHOLDER_WORDWRAP = "\x2 WORDWRAP \x3";

	private static $length = 80;

	private static $tabSizeInSpace = 8;

	public function candidate($source, $foundTokens) {
		return true;
	}

	public function format($source) {
		$this->tkns = token_get_all($source);
		$this->code = '';

		$currentLineLength = 0;
		while (list($index, $token) = eachArray($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;

			$originalText = $text;
			if (T_WHITESPACE == $id) {
				$text = str_replace(
					$this->indentChar,
					str_repeat(' ', self::$tabSizeInSpace),
					$text
				);
			}
			$textLen = strlen($text);

			$currentLineLength += $textLen;

			if ($this->hasLn($text)) {
				$currentLineLength = $textLen - strrpos($text, $this->newLine);
			}

			if ($currentLineLength > self::$length) {
				$currentLineLength = $textLen - strrpos($text, $this->newLine);
				$this->code = str_replace(self::PLACEHOLDER_WORDWRAP, $this->newLine, $this->code);
			}

			if (T_OBJECT_OPERATOR == $id || T_WHITESPACE == $id) {
				$this->code = str_replace(self::PLACEHOLDER_WORDWRAP, '', $this->code);
				$this->appendCode(self::PLACEHOLDER_WORDWRAP);
			}
			$this->appendCode($originalText);
		}

		$this->code = str_replace(self::PLACEHOLDER_WORDWRAP, '', $this->code);
		return $this->code;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getDescription() {
		return 'Word wrap at 80 columns - left justify.';
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function getExample() {
		return '';
	}
}