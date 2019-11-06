clc; clear;
% Interpolate data of air temperature over a common grid

folder = '/Volumes/PTV #2/rda/ice_2019/out';
outFolder = '/Volumes/PTV #2/rda/ice_2019/out_interp';
files = dir([folder, '/gdas1*.txt']);

% mkdir(outFolder);
d = 2.5;
latitudeSeries = -90:d:90;
longitudeSeries = -180:d:180;
[latidueGrid, longitudeGrid] = meshgrid(latitudeSeries, longitudeSeries);
totalFiles = length(files);
interpT = NaN(size(longitudeGrid, 1), size(longitudeGrid, 2), totalFiles);

% timeVector = readtable('/Volumes/PTV #2/rda/ice_2019/out/time.txt');
% timeVector = timeVector.Var1;
a = 1;
for f=1:totalFiles
    
    fileName = files(f).name;
    fullFile = fullfile(folder, fileName);
    outFullFile = strrep(fullfile(folder, fileName), folder, outFolder);
    if(strcmp(fileName(1:2), '._'))
        continue;
    end
    
    fprintf(sprintf('>> Parsing (%d/%d) %s\n', f, totalFiles, fileName));
    data = readtable(fullFile, 'ReadVariableNames', false, 'Delimiter', ' ', ...
        'MultipleDelimsAsOne', true, 'HeaderLines', 1);
    F = scatteredInterpolant(data.Var1, data.Var2, data.Var3);
    interpT(:, :, a) = F(latidueGrid, longitudeGrid);
    
    matchStr = regexp(fileName, '\w*(\d{2})+', 'match');
    timeVector(a, 1) = datetime(matchStr{1}, 'InputFormat', 'yyyyMMddHH');
    
    a = a + 1;
end
save(fullfile(folder, 'interpData.mat'), 'interpT', 'latitudeSeries', ...
    'longitudeSeries', 'latidueGrid', 'longitudeGrid', 'timeVector');