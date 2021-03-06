﻿using System;
using System.Collections.Generic;
using System.Text;
using System.IO;
using System.Threading;

namespace Borhan
{
    class BorhanUploadThread
    {
        public string ks;
        public FileStream file;
        public string uploadTokenId;
        public int chunkSize;


        /*
         * position => uploaded
         */
        public LinkedList<int> ranges;

        public BorhanUploadThread(string ks, FileStream file, int chunkSize, LinkedList<int> ranges, string uploadTokenId)
        {
            this.chunkSize = chunkSize;
            this.file = file;
            this.ks = ks;
            this.ranges = ranges;
            this.uploadTokenId = uploadTokenId;
        }

        public void upload()
        {
            BorhanClient client = new BorhanClient(BorhanClientTester.GetConfig());
            client.KS = ks;

            if(ranges.Count.Equals(0))
            {
                // no more items - avoid null pointer exception
                BorhanClientTester.workingThreads--;
                return;
            }
            byte[] chunk = new byte[this.chunkSize];
            int index = ranges.Last.Value;
            // mark as uploaded
            ranges.RemoveLast();
            // read part of file
            file.Seek(index, SeekOrigin.Begin);
            int bytesRead = file.Read(chunk, 0, this.chunkSize);
            long resumeAt = index;

            try
            {

                Stream chunkFile = new MemoryStream(chunk);
                client.UploadTokenService.Upload(uploadTokenId, chunkFile, true, false, resumeAt);
                chunkFile.Close();                
            }
            catch (BorhanAPIException ex)
            {
                Console.WriteLine("failed to upload and resume at position "+resumeAt + " message: ["+ex.Message+"] replacing last index "+index);
                // put chunk start position back in the queue so will be picked by next thread
                ranges.AddLast(index);
            }
            finally
            {
                BorhanClientTester.workingThreads--;
            }
        }
    }
}
