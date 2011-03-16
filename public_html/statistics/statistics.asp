<%
 ' Author:        Marc Gerritsen (m.gerritsen@minvws.nl)
 ' Version:       0.5 (27-03-2007)
 ' Description:   This file writes status, ip, date, filename to statistics_logfile.txt (status = start or pause or text or fullscreen or completed or mute)
 ' Info:          For a php version check jeroen's flvplayer site (http://www.jeroenwijering.com/?item=Flash_Video_Player)
 '
  Dim strLogFile, strMoviename, strIPAdres, strDateTime, strStatus, iPostion
  Dim objFileSystem, objFile

  iPosition    = inStrRev(Request.ServerVariables("PATH_TRANSLATED"), "\")
  strLogFile   = left(Request.ServerVariables("PATH_TRANSLATED"), iPosition) & "statistics_logfile.txt"
  'response.write strLogFile 
  strMoviename = Request.Form("file")
  strIPAdres   = Request.ServerVariables("REMOTE_ADDR")
  strDateTime  = now
  strStatus    = Request.Form("state")

  'open the file for writing / appending
  Set objFileSystem = CreateObject("Scripting.FileSystemObject")
  Set objFile = objFileSystem.OpenTextFile(strLogFile, 8, True)

  if strMoviename <> "" then
    Call objFile.Write(strMoviename & "," & strIPAdres & "," & strDateTime & "," & strStatus & vbCrLf)
  end if
  objFile.Close

  
  Set objFileSystem = Noting
  Set objFile = Noting
  
%>