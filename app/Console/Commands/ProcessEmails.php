<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpMimeMailParser\Parser;
use Soundasleep\Html2Text\Html2Text;

/**
 * Command to process raw email content and save plain text body.
 *
 * This command is responsible for extracting plain text from HTML email content and updating the database with the processed text.
 */
class ProcessEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process raw email content and save plain text body';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Processes all emails with empty raw text and updates the database with the extracted plain text.
     */
    public function handle()
    {

        // Get the argument.
        $arguments = $this->arguments();
        $id = null;
        if(isset($arguments['id'])) $id = $arguments['id'];
        if(isset($arguments[1])) $id = $arguments[1];


        // Define the base of condition..
        // The raw_text cannot be null, so i used a empty string to check the unprocessed rows.
        // $whereCondition = ['raw_text' => ''];
        $whereCondition = [];

        // If id was passed..
        if (isset($id)) $whereCondition['id'] = $id;

        // Retrieve all emails with empty raw text
        $emails = DB::table('successful_emails')
          ->where($whereCondition)
          ->get();

        // Process each email and update the database
        foreach ($emails as $email) {
            $text = $this->extractPlainText($email->email);

            // $this->info($text);
            DB::table('successful_emails')
                ->where('id', $email->id)
                ->update(['raw_text' => $text]);
        }

        // Output a success message
        $this->info('Emails processed successfully.');
    }

    /**
     * Fix the urls encoding
     */
    private function extractUrls($emailContent) {

      // Find the urls
      $pattern = '/https?:\/\/[^\s"]+/i';
      preg_match_all($pattern, $emailContent, $matches);
      return $matches[0];
    }

    /**
     * Extract plain text from HTML content.
     *
     * Uses the Html2Text library to convert HTML content to plain text.
     * https://github.com/soundasleep/html2text
     *
     * @param string $html HTML content to extract plain text from
     * @return string Extracted plain text
     */
    protected function extractPlainText($emailContent)
    {

      // Convert breakline to unix default
      $emailContent = str_replace("\r\n", "\n", $emailContent);

      // Remove comments and style tags
      $emailContent = preg_replace('/<style[\s\S]*?<\/style>|<!--[\s\S]*?-->|\/\*[\s\S]*?\*\//', '', $emailContent);

      // Remove html tags
      $emailContent = preg_replace('/<[^>]*>/', '', $emailContent);

      // Returned string output.
      $out = '';

      // First try to find the multipart alternative
      $multipartPattern = '/Content-Type:\s*multipart\/alternative;\s*boundary="([^"]+)"/i';

      // Extract all url's from string
      $urls = $this->extractUrls($emailContent);

      // Use a marker in a url place.
      $preserveMarker = '##URL##';
      $emailContent = preg_replace_callback('/(https?:\/\/[^\s"]+)/i',
        function($matches) use ($preserveMarker) {
          static $index = 0;
          return $preserveMarker . ($index++) . $preserveMarker;
        },
        $emailContent
      );

      // Check if have a multipart pattern.
      if (preg_match($multipartPattern, $emailContent, $matches)) {
        $boundary = $matches[1];

        // Extract the first boundary value if exists.
        $contentPattern = '/--' . preg_quote($boundary, '/') . '\s*(.*?)\s*(?=--' . preg_quote($boundary, '/') . '|--' . preg_quote($boundary, '/') . '--)/s';
        if (preg_match($contentPattern, $emailContent, $contentMatches)) {

          // Implement the output and remove the information about the content..
          $out.= preg_replace('/^Content-Type:\s*text\/plain;.*\n|^Content-Transfer-Encoding:.*\n/m', '', $contentMatches[1]);
          $out = quoted_printable_decode($out);
        }
      } else {

        // Try to find the blocks with Content-Type prefix..
        // Consider the content of content-type after skipping two lines and ending with a newline in --
        $pattern = '/Content-Type: text\/.*?\n\n(.*?)(?=--|$)/s';
        if(preg_match_all($pattern, $emailContent, $matches)) {

          // Loop thougth the blocks
          foreach($matches[1] as $k=>$t) {

            // Identify attachment...not use right now.
            if (preg_match('/Content-Transfer-Encoding:\s*base64/', $matches[0][$k])) continue;

            // Decode the "quoted-printable"
            $t = quoted_printable_decode($t);

            // Remove tags..
            $plainText = preg_replace('/<[^>]*>/', '', trim($t));

            // Implement the output
            $out.= $plainText."\n";
          }

          // Substract the excedent final break-line.
          $out = substr($out, 0, -1);

        } else {

          // If don't find any block, the mail will be more simple..
          // So i find the first blank line after the head.
          if(preg_match('/^(.*?)(?:\r?\n\r?\n)(.*)$/s', $emailContent, $matches)) {

            // Find in secound possition of regex.
            if (isset($matches[2])) {
                // Remove tags..
                $out = preg_replace('/<[^>]*>/', '', $matches[2]);
            }
          }
        }
      }

      // Return the urls extracted.
      foreach ($urls as $index => $url) {
        $out = str_replace($preserveMarker . $index . $preserveMarker, $url, $out);
      }

      // $out = preg_replace('/\s+/', ' ', $out);
      $out = preg_replace('/[ \t]+/', ' ', $out);

      // Correct the html entities.
      //$out = html_entity_decode($out);

      // Remove multiple breaklines
      $out = preg_replace('/(\r?\n){2,}/', "\n", $out);

      // Remove the spaces excess and multiple blank lines.
      $lines = explode("\n", $out);
      $lines = array_map('trim', $lines);
      $lines = array_filter($lines, function($line) { return !empty($line); });
      $out = implode("\n", $lines);

      return $out;
    }
}
