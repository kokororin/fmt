<?php
# Copyright (c) 2015, phpfmt and its authors
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

final class PSR1OpenTags extends FormatterPass {
	public function candidate(string $source, array $foundTokens): bool {
		return true;
	}

	public function format(string $source): string{
		$this->tkns = token_get_all($source);
		$this->code = '';
		$touchedComment = false;
		while (list($index, $token) = each($this->tkns)) {
			list($id, $text) = $this->getToken($token);
			$this->ptr = $index;
			switch ($id) {
			case T_OPEN_TAG:
				if ('<?php' !== $text) {
					$this->appendCode('<?php' . ($this->hasLnAfter() || $this->hasLn($text) || $this->rightUsefulTokenIs(T_NAMESPACE) ? $this->newLine : $this->getSpace()));
					break;
				}
				$this->appendCode($text);
				break;

			case T_CLOSE_TAG:
				if (!$touchedComment && !$this->leftUsefulTokenIs([ST_SEMI_COLON, ST_COLON, ST_CURLY_CLOSE, ST_CURLY_OPEN])) {
					$this->appendCode(ST_SEMI_COLON);
				}
				$touchedComment = false;
				$this->appendCode($text);
				break;

			case T_COMMENT:
			case T_DOC_COMMENT:
				if (
					$this->rightUsefulTokenIs([T_CLOSE_TAG]) &&
					!$this->leftUsefulTokenIs([ST_SEMI_COLON]) &&
					!$this->leftUsefulTokenIs([T_OPEN_TAG])
				) {
					$touchedComment = true;
					$this->rtrimAndappendCode(ST_SEMI_COLON . ' ');
				}
				$this->appendCode($text);
				break;

			default:
				$this->appendCode($text);
				break;
			}
		}
		return $this->code;
	}
}
